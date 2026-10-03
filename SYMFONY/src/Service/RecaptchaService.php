<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class RecaptchaService
{
    private const GOOGLE_VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';
    private const SCORE_THRESHOLD = 0.3; // Temporarily lowered for testing (normal: 0.5)

    public function __construct(
        private HttpClientInterface $httpClient,
        private string $recaptchaSecretKey,
    ) {}

    /**
     * Verify reCAPTCHA v3 token with Google's verification service
     *
     * @param string $token The reCAPTCHA token from frontend
     * @return array{success: bool, score: float, action: string, challenge_ts: string, hostname: string}
     */
    public function verify(string $token): array
    {
        if (empty($token)) {
            return ['success' => false, 'score' => 0];
        }

        try {
            $response = $this->httpClient->request('POST', self::GOOGLE_VERIFY_URL, [
                'body' => [
                    'secret' => $this->recaptchaSecretKey,
                    'response' => $token,
                ],
            ]);

            $data = $response->toArray();

            // Log for debugging
            error_log('reCAPTCHA Response: ' . json_encode($data));

            return [
                'success' => $data['success'] ?? false,
                'score' => $data['score'] ?? 0,
                'action' => $data['action'] ?? '',
                'challenge_ts' => $data['challenge_ts'] ?? '',
                'hostname' => $data['hostname'] ?? '',
                'error_codes' => $data['error-codes'] ?? [],
            ];
        } catch (\Exception $e) {
            error_log('reCAPTCHA Error: ' . $e->getMessage());
            return [
                'success' => false,
                'score' => 0,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check if reCAPTCHA v3 response is valid (score above threshold)
     * v3 scores: 1.0 = very likely human, 0.0 = very likely bot
     */
    public function isValid(string $token): bool
    {
        // Bypass reCAPTCHA on localhost/dev
        if (in_array($_SERVER['SERVER_NAME'] ?? '', ['localhost', '127.0.0.1', '::1'])) {
            return true;
        }

        $result = $this->verify($token);
        return $result['success'] === true && $result['score'] >= self::SCORE_THRESHOLD;
    }

    /**
     * Get the score for advanced decision-making
     */
    public function getScore(string $token): float
    {
        $result = $this->verify($token);
        return $result['score'] ?? 0;
    }
}
