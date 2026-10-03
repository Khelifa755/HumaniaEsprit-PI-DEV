<?php

namespace App\Service\Utilisateur;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class FaceRecognitionService
{
    private const API_KEY = 'FQgIMhdXPT9cW0jgqI_CdQmylIT6AY3m';
    private const API_SECRET = '5pim-LdfBcKeSxQzhpIznI_Tjus5ecOH';
    private const FACESET_OUTER_ID = 'humania_users';
    
    public function __construct(private readonly HttpClientInterface $client) {}

    public function detectFace(string $base64): ?string
    {
        if (str_contains($base64, ',')) {
            $base64 = explode(',', $base64)[1];
        }

        // Add a small delay to respect Face++ free tier concurrency limits
        sleep(1);

        $response = $this->client->request('POST', 'https://api-us.faceplusplus.com/facepp/v3/detect', [
            'body' => [
                'api_key' => self::API_KEY,
                'api_secret' => self::API_SECRET,
                'image_base64' => $base64
            ]
        ]);
        
        $data = $response->toArray(false);
        if (isset($data['faces'][0]['face_token'])) {
            return $data['faces'][0]['face_token'];
        }
        
        if (isset($data['error_message'])) {
            throw new \RuntimeException("Face++ Error: " . $data['error_message']);
        }
        
        return null;
    }

    public function addFaceToSet(string $faceToken): void
    {
        sleep(1);
        $response = $this->client->request('POST', 'https://api-us.faceplusplus.com/facepp/v3/faceset/addface', [
            'body' => [
                'api_key' => self::API_KEY,
                'api_secret' => self::API_SECRET,
                'outer_id' => self::FACESET_OUTER_ID,
                'face_tokens' => $faceToken
            ]
        ]);
        $data = $response->toArray(false);

        if (isset($data['error_message'])) {
            if ($data['error_message'] === 'INVALID_OUTER_ID') {
                // FaceSet doesn't exist, create it
                sleep(1);
                $this->client->request('POST', 'https://api-us.faceplusplus.com/facepp/v3/faceset/create', [
                    'body' => [
                        'api_key' => self::API_KEY,
                        'api_secret' => self::API_SECRET,
                        'outer_id' => self::FACESET_OUTER_ID
                    ]
                ]);
                // Retry adding face
                sleep(1);
                $retryResponse = $this->client->request('POST', 'https://api-us.faceplusplus.com/facepp/v3/faceset/addface', [
                    'body' => [
                        'api_key' => self::API_KEY,
                        'api_secret' => self::API_SECRET,
                        'outer_id' => self::FACESET_OUTER_ID,
                        'face_tokens' => $faceToken
                    ]
                ]);
                $retryData = $retryResponse->toArray(false);
                if (isset($retryData['error_message'])) {
                    throw new \RuntimeException("Face++ AddFace Retry Error: " . $retryData['error_message']);
                }
            } else {
                throw new \RuntimeException("Face++ AddFace Error: " . $data['error_message']);
            }
        }
    }

    public function removeFaceFromSet(string $faceToken): void
    {
        sleep(1);
        $response = $this->client->request('POST', 'https://api-us.faceplusplus.com/facepp/v3/faceset/removeface', [
            'body' => [
                'api_key' => self::API_KEY,
                'api_secret' => self::API_SECRET,
                'outer_id' => self::FACESET_OUTER_ID,
                'face_tokens' => $faceToken
            ]
        ]);
        $data = $response->toArray(false);

        if (isset($data['error_message'])) {
            // If the faceset or token doesn't exist anymore, it's fine. We just want it removed.
            // Still, we can log it or throw if it's a critical error, but usually we just proceed.
            if ($data['error_message'] !== 'INVALID_OUTER_ID' && !str_contains($data['error_message'], 'FACE_TOKEN_NOT_FOUND')) {
                throw new \RuntimeException("Face++ RemoveFace Error: " . $data['error_message']);
            }
        }
    }

    public function searchFace(string $base64): ?string
    {
        if (str_contains($base64, ',')) {
            $base64 = explode(',', $base64)[1];
        }

        sleep(1);
        $response = $this->client->request('POST', 'https://api-us.faceplusplus.com/facepp/v3/search', [
            'body' => [
                'api_key' => self::API_KEY,
                'api_secret' => self::API_SECRET,
                'image_base64' => $base64,
                'outer_id' => self::FACESET_OUTER_ID
            ]
        ]);

        $data = $response->toArray(false);
        
        if (isset($data['error_message'])) {
            // Might be INVALID_OUTER_ID if faceset is empty
            return null;
        }

        if (isset($data['results'][0])) {
            $bestMatch = $data['results'][0];
            // Compare confidence to 1e-4 threshold
            if (isset($data['thresholds']['1e-4']) && $bestMatch['confidence'] >= $data['thresholds']['1e-4']) {
                return $bestMatch['face_token'];
            }
        }
        
        return null;
    }
}
