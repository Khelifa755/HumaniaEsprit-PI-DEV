<?php

namespace App\Controller\Utilisateur;

use App\Entity\Utilisateur;
use App\Security\Utilisateur\LoginFormAuthenticator;
use App\Service\Utilisateur\MfaService;
use App\Service\Utilisateur\UtilisateurService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TwoFactorController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly MfaService $mfaService,
        private readonly UtilisateurService $utilisateurService,
        private readonly Security $security,
    ) {}

    #[Route('/login/mfa', name: 'app_login_mfa', methods: ['GET', 'POST'])]
    public function form(Request $request): Response
    {
        $session = $request->getSession();
        $pendingUserId = $session->get('mfa_pending_user_id');

        if (!$pendingUserId) {
            return $this->redirectToRoute('app_login');
        }

        /** @var Utilisateur|null $user */
        $user = $this->em->getRepository(Utilisateur::class)->find((int) $pendingUserId);
        if (!$user || !$user->isMfaEnabled() || !$user->getMfaSecret()) {
            $session->remove('mfa_pending_user_id');
            $session->remove('mfa_target_path');
            $this->addFlash('error', 'Session MFA invalide. Veuillez vous reconnecter.');
            return $this->redirectToRoute('app_login');
        }

        if ($request->isMethod('POST')) {
            $code = trim((string) $request->request->get('_auth_code', ''));
            if (!$this->mfaService->verifyCode($user->getMfaSecret(), $code)) {
                $this->addFlash('error', 'Code invalide. Veuillez réessayer.');
            } else {
                $targetPath = (string) $session->get('mfa_target_path', '');
                $session->remove('mfa_pending_user_id');
                $session->remove('mfa_target_path');
                $session->set('mfa_verified', true);

                $this->security->login($user, LoginFormAuthenticator::class, 'main');
                $this->utilisateurService->setOnline($user->getId(), true);

                if ($targetPath !== '') {
                    $targetPathPath = parse_url($targetPath, PHP_URL_PATH) ?: $targetPath;
                    if ($targetPathPath !== '/') {
                        return $this->redirect($targetPath);
                    }
                }

                return $this->redirectToRoute($this->resolvePostMfaRoute($user));
            }
        }

        return $this->render('Utilisateur/security/2fa.html.twig');
    }

    private function resolvePostMfaRoute(Utilisateur $user): string
    {
        return match ($user->getRole()?->value) {
            'ADMIN'     => 'app_admin_dashboard',
            'RH'        => 'app_rh_dashboard',
            'MANAGER'   => 'app_manager_dashboard',
            'FORMATEUR' => 'app_formateur_dashboard',
            default     => 'app_home',
        };
    }
}
