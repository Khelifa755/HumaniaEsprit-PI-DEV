<?php

namespace App\Security\Utilisateur;

use App\Service\Utilisateur\UtilisateurService;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use League\OAuth2\Client\Provider\GoogleUser;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

/**
 * Replaces Java GoogleAuthService.
 *
 * Desktop Java used a local HTTP server on :8080.
 * Symfony uses the standard OAuth2 redirect flow via knpuniversity/oauth2-client-bundle.
 *
 * Flow:
 *  1. User clicks "Se connecter avec Google" → /connect/google (SecurityController)
 *  2. Google redirects back → /connect/google/check
 *  3. This authenticator fires, fetches Google user info, validates email against DB.
 */
class GoogleAuthenticator extends OAuth2Authenticator
{
    public function __construct(
        private readonly ClientRegistry        $clientRegistry,
        private readonly UtilisateurService    $utilisateurService,
        private readonly RouterInterface       $router,
    ) {}

    public function supports(Request $request): bool
    {
        return $request->attributes->get('_route') === 'connect_google_check';
    }

    public function authenticate(Request $request): Passport
    {
        $client      = $this->clientRegistry->getClient('google');
        $accessToken = $this->fetchAccessToken($client);

        return new SelfValidatingPassport(
            new UserBadge($accessToken->getToken(), function () use ($accessToken, $client) {
                /** @var GoogleUser $googleUser */
                $googleUser = $client->fetchUserFromToken($accessToken);
                $email      = $googleUser->getEmail();

                try {
                    return $this->utilisateurService->connecterViaGoogle($email);
                } catch (\RuntimeException $e) {
                    throw new CustomUserMessageAuthenticationException($e->getMessage());
                }
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): Response
    {
        $user = $token->getUser();
        if (method_exists($user, 'getId') && $user->getId()) {
            $this->utilisateurService->setOnline($user->getId(), true);
        }
        return new RedirectResponse($this->router->generate('app_home'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        $request->getSession()->getFlashBag()->add(
            'error',
            'Connexion Google refusée : ' . $exception->getMessage()
        );
        return new RedirectResponse($this->router->generate('app_login'));
    }
}