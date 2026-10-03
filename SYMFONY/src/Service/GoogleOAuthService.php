<?php

namespace App\Service;

use App\Entity\Utilisateur;
use App\Enum\Role;
use App\Repository\Utilisateur\UtilisateurRepository;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GoogleOAuthService
{
    private const GOOGLE_AUTH_URL = 'https://accounts.google.com/o/oauth2/v2/auth';
    private const GOOGLE_TOKEN_URL = 'https://oauth2.googleapis.com/token';
    private const GOOGLE_USERINFO_URL = 'https://www.googleapis.com/oauth2/v2/userinfo';

    public function __construct(
        private HttpClientInterface $httpClient,
        private UtilisateurRepository $utilisateurRepository,
        private string $googleClientId,
        private string $googleClientSecret,
    ) {}

    /**
     * Generate Google OAuth authorization URL
     */
    public function getAuthorizationUrl(string $redirectUri, ?string $state = null): string
    {
        $state = $state ?? bin2hex(random_bytes(16));

        return sprintf(
            '%s?%s',
            self::GOOGLE_AUTH_URL,
            http_build_query([
                'client_id' => $this->googleClientId,
                'redirect_uri' => $redirectUri,
                'response_type' => 'code',
                'scope' => 'openid email profile',
                'state' => $state,
                'access_type' => 'offline',
            ])
        );
    }

    /**
     * Exchange authorization code for access token and get user info
     *
     * @return array{user: Utilisateur|null, email: string|null, error: string|null}
     */
    public function handleCallback(string $code, string $redirectUri): array
    {
        try {
            // Exchange code for token
            $tokenResponse = $this->httpClient->request('POST', self::GOOGLE_TOKEN_URL, [
                'body' => [
                    'client_id' => $this->googleClientId,
                    'client_secret' => $this->googleClientSecret,
                    'code' => $code,
                    'grant_type' => 'authorization_code',
                    'redirect_uri' => $redirectUri,
                ],
            ]);

            $tokenData = $tokenResponse->toArray();

            if (!isset($tokenData['access_token'])) {
                return ['user' => null, 'email' => null, 'error' => 'Failed to obtain access token'];
            }

            // Get user info
            $userResponse = $this->httpClient->request('GET', self::GOOGLE_USERINFO_URL, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $tokenData['access_token'],
                ],
            ]);

            $userData = $userResponse->toArray();

            if (!isset($userData['email'])) {
                return ['user' => null, 'email' => null, 'error' => 'Failed to get user email from Google'];
            }

            // Find or create user
            $email = $userData['email'];
            $user = $this->utilisateurRepository->findOneBy(['email' => $email]);

            if (!$user) {
                // Auto-create user with Google email
                $user = new Utilisateur();
                $user->setEmail($email);
                $user->setUsername(explode('@', $email)[0]); // Use email prefix as username
                $user->setPrenom($userData['given_name'] ?? '');
                $user->setNom($userData['family_name'] ?? '');
                $user->setPdp($userData['picture'] ?? null);
                $user->setRole(Role::EMPLOYE); // Default role
                $user->setStatut('Actif');
                // No password for OAuth users
                $user->setMotDePasse(null);

                $this->utilisateurRepository->save($user, flush: true);
            }

            return ['user' => $user, 'email' => $email, 'error' => null];
        } catch (\Exception $e) {
            return ['user' => null, 'email' => null, 'error' => $e->getMessage()];
        }
    }
}
