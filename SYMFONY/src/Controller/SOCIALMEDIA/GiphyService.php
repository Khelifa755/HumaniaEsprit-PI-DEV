<?php

namespace App\Controller\SOCIALMEDIA;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class GiphyService
{
    private const API_KEY = 'ssxr2LTh5SjyJL4AEtnqTvpYRycFcU9q';
    private const BASE_URL = 'https://api.giphy.com/v1/gifs';

    public function __construct(private HttpClientInterface $httpClient) {}

    /**
     * Search GIFs by query
     * @return array<array{id: string, previewUrl: string, originalUrl: string, title: string}>
     */
    public function search(string $query, int $limit = 10): array
    {
        try {
            $url = self::BASE_URL . '/search?api_key=' . self::API_KEY
                    . '&q=' . urlencode($query)
                    . '&limit=' . $limit
                    . '&rating=g';

            return $this->fetchGifs($url);
        } catch (\Exception $e) {
            error_log('❌ Giphy search error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get trending GIFs
     * @return array<array{id: string, previewUrl: string, originalUrl: string, title: string}>
     */
    public function trending(int $limit = 10): array
    {
        try {
            $url = self::BASE_URL . '/trending?api_key=' . self::API_KEY
                    . '&limit=' . $limit
                    . '&rating=g';

            return $this->fetchGifs($url);
        } catch (\Exception $e) {
            error_log('❌ Giphy trending error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Fetch GIFs from API
     */
    private function fetchGifs(string $url): array
    {
        try {
            $response = $this->httpClient->request('GET', $url);
            $data = $response->toArray();
            return $this->parseGiphyResponse($data);
        } catch (\Exception $e) {
            error_log('❌ Giphy fetch error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Parse Giphy API response
     */
    private function parseGiphyResponse(array $json): array
    {
        $results = [];

        try {
            if (!isset($json['data']) || !is_array($json['data'])) {
                return $results;
            }

            foreach ($json['data'] as $item) {
                $id = $item['id'] ?? null;
                $title = $item['title'] ?? '';

                // Preview URL (fixed_width for grid)
                $previewUrl = null;
                if (isset($item['images']['fixed_width']['url'])) {
                    $previewUrl = $item['images']['fixed_width']['url'];
                }

                // Original URL (original size)
                $originalUrl = null;
                if (isset($item['images']['original']['url'])) {
                    $originalUrl = $item['images']['original']['url'];
                }

                if ($id && $previewUrl) {
                    $results[] = [
                        'id' => $id,
                        'previewUrl' => $previewUrl,
                        'originalUrl' => $originalUrl ?? $previewUrl,
                        'title' => $title
                    ];
                }
            }
        } catch (\Exception $e) {
            error_log('❌ Parse error: ' . $e->getMessage());
        }

        return $results;
    }
}
