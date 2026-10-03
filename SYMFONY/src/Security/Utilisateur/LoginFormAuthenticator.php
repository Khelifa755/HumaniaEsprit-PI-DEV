<?php

namespace App\Security\Utilisateur;

use App\Entity\Utilisateur;
use App\Service\RecaptchaService;
use App\Service\Utilisateur\UtilisateurService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

/**
 * Replaces Java LoginController.
 *
 * Brute-force protection (session-based):
 *   3 failed attempts  → 60-second lockout stored in $_SESSION
 *   Mirrors Java's failedAttempts / locked / scheduler logic exactly.
 */
class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    private const MAX_ATTEMPTS    = 3;
    private const LOCKOUT_SECONDS = 60;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UtilisateurService    $utilisateurService,
        private readonly RecaptchaService      $recaptchaService,
        private readonly RouterInterface       $router,
        private readonly TokenStorageInterface $tokenStorage,
    ) {}

    public function supports(Request $request): bool
    {
        return $request->isMethod('POST')
            && $request->attributes->get('_route') === 'app_login';
    }

    public function authenticate(Request $request): Passport
    {
        $email    = trim((string) $request->request->get('_username', ''));
        $password = trim((string) $request->request->get('_password', ''));
        $csrf     = (string) $request->request->get('_csrf_token', '');
        $recaptchaToken = (string) $request->request->get('g-recaptcha-response', '');
        $session  = $request->getSession();

        // ── reCAPTCHA verification ────────────────────────────────────────────
        if (!$this->recaptchaService->isValid($recaptchaToken)) {
            throw new CustomUserMessageAuthenticationException(
                'Veuillez vérifier le reCAPTCHA.'
            );
        }

        // ── Lockout check ─────────────────────────────────────────────────────
        $lockoutUntil = $session->get('lockout_until');
        if ($lockoutUntil && time() < $lockoutUntil) {
            $remaining = $lockoutUntil - time();
            throw new CustomUserMessageAuthenticationException(
                "Trop de tentatives échouées. Réessayez dans $remaining seconde(s)."
            );
        }

        return new Passport(
            new UserBadge($email, function (string $email) use ($password, $session) {

                $hashed = $this->utilisateurService->hashPassword($password);
                $repo   = $this->em->getRepository(Utilisateur::class);
                $u      = $repo->findByEmailAndPassword($email, $hashed);

                if ($u === null) {
                    // ── Wrong credentials ─────────────────────────────────────
                    $attempts = ((int) $session->get('login_attempts', 0)) + 1;
                    $session->set('login_attempts', $attempts);

                    if ($attempts >= self::MAX_ATTEMPTS) {
                        $session->set('lockout_until', time() + self::LOCKOUT_SECONDS);
                        $session->set('login_attempts', 0);
                        throw new CustomUserMessageAuthenticationException(
                            'Compte temporairement bloqué (60 secondes). Trop de tentatives échouées.'
                        );
                    }

                    $remaining = self::MAX_ATTEMPTS - $attempts;
                    throw new CustomUserMessageAuthenticationException(
                        "Email ou mot de passe incorrect. Il vous reste $remaining tentative(s)."
                    );
                }

                // ── Account status check ──────────────────────────────────────
                $statut = strtolower(trim($u->getStatut() ?? ''));
                if ($statut === 'bloque') {
                    throw new CustomUserMessageAuthenticationException('Votre compte est bloqué.');
                }
                if ($statut === 'suspendu') {
                    throw new CustomUserMessageAuthenticationException('Votre compte est suspendu.');
                }
                if ($statut === 'archive') {
                    throw new CustomUserMessageAuthenticationException('Votre compte est archivé.');
                }

                // ── Success — reset counter ───────────────────────────────────
                $session->remove('login_attempts');
                $session->remove('lockout_until');

                return $u;
            }),
            new CustomCredentials(static fn() => true, $password),
            [
                new CsrfTokenBadge('authenticate', $csrf),
                new RememberMeBadge(),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): Response
    {
        /** @var Utilisateur $user */
        $user = $token->getUser();
        $session = $request->getSession();

        // If user already passed MFA on /login/mfa, skip MFA challenge here.
        $mfaAlreadyVerified = (bool) $session->get('mfa_verified', false);
        if ($mfaAlreadyVerified) {
            $session->remove('mfa_verified');
        }

        if (!$mfaAlreadyVerified && $user->isMfaEnabled() && $user->getMfaSecret()) {
            $session->set('mfa_pending_user_id', $user->getId());
            $session->set('mfa_target_path', $this->getTargetPath($session, $firewallName) ?: $this->getSuccessUrl($user));

            // Cancel the full-auth token until MFA code is validated.
            $this->tokenStorage->setToken(null);

            return new RedirectResponse($this->router->generate('app_login_mfa'));
        }

        $this->utilisateurService->setOnline($user->getId(), true);

        // Keep role-based dashboard as target so MFA flow redirects correctly after code verification.
        if (!$this->getTargetPath($request->getSession(), $firewallName)) {
            $this->saveTargetPath($request->getSession(), $firewallName, $this->getSuccessUrl($user));
        }

        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            $targetPathPath = parse_url($targetPath, PHP_URL_PATH) ?: $targetPath;

            // If user logged in from landing page "/", ignore that target and use role dashboard.
            if ($targetPathPath !== '/') {
                return new RedirectResponse($targetPath);
            }
        }

        return new RedirectResponse($this->getSuccessUrl($user));
    }

    private function getSuccessUrl(Utilisateur $user): string
    {
        return match ($user->getRole()?->value) {
            'ADMIN'     => $this->router->generate('app_admin_dashboard'),
            'RH'        => $this->router->generate('app_rh_dashboard'),
            'MANAGER'   => $this->router->generate('app_manager_dashboard'),
            'FORMATEUR' => $this->router->generate('app_formateur_dashboard'),
            default     => $this->router->generate('app_home'),
        };
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->router->generate('app_login');
    }
}