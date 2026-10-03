<?php

namespace App\Controller\SOCIALMEDIA;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class GroqAIService
{
    private const API_KEY = null;
    private const API_URL = 'https://api.groq.com/openai/v1/chat/completions';
    private const MODEL = 'llama-3.3-70b-versatile';

    public function __construct(private HttpClientInterface $httpClient) {}

    /**
     * Improve text for professional social network
     */
    public function improveText(string $text): ?string
    {
        $prompt = "Tu es un assistant RH professionnel. " .
                "Améliore et reformule ce texte pour une publication sur un réseau social d'entreprise. " .
                "Garde le même sens, rends-le plus professionnel et engageant. " .
                "Réponds UNIQUEMENT avec le texte amélioré, sans explication.\n\nTexte : " . $text;

        return $this->callGroq($prompt);
    }

    /**
     * Summarize text into 2-3 sentences
     */
    public function summarize(string $text): ?string
    {
        $prompt = "Résume ce texte en 2-3 phrases claires et concises en français. " .
                "Réponds UNIQUEMENT avec le résumé, sans introduction ni explication.\n\nTexte : " . $text;

        return $this->callGroq($prompt);
    }

    /**
     * Generate post based on subject and tone
     */
    public function generatePost(string $subject, string $tone = 'professionnel'): ?string
    {
        $prompt = "Tu es un expert en communication RH. " .
                "Rédige un post " . $tone . " pour un réseau social d'entreprise sur le sujet suivant : " .
                $subject . ". " .
                "Le post doit être engageant, entre 3 et 6 phrases, avec des emojis appropriés. " .
                "Réponds UNIQUEMENT avec le texte du post, sans titre ni explication.";

        return $this->callGroq($prompt);
    }

    /**
     * Call Groq API
     */
    private function callGroq(string $prompt): ?string
    {
        try {
            $body = [
                'model' => self::MODEL,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'max_tokens' => 500,
                'temperature' => 0.7
            ];

            $response = $this->httpClient->request('POST', self::API_URL, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . self::API_KEY,
                ],
                'json' => $body,
            ]);

            $data = $response->toArray();
            return $this->parseGroqResponse($data);
        } catch (\Exception $e) {
            error_log('❌ Groq error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Parse Groq response
     */
    private function parseGroqResponse(array $data): ?string
    {
        try {
            if (isset($data['choices'][0]['message']['content'])) {
                return trim($data['choices'][0]['message']['content']);
            }
            return null;
        } catch (\Exception $e) {
            error_log('❌ Parse error: ' . $e->getMessage());
            return null;
        }
    }
}
