<?php

namespace App\Service\Plannification;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Zoom Server-to-Server OAuth + REST API (meetings).
 *
 * Env: ZOOM_ACCOUNT_ID, ZOOM_CLIENT_ID, ZOOM_CLIENT_SECRET, ZOOM_USER_ID
 */
final class ZoomService
{
    private const TOKEN_URL = 'https://zoom.us/oauth/token';

    private const API_BASE = 'https://api.zoom.us/v2';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly CacheInterface $cache,
        private readonly LoggerInterface $logger,
        private readonly string $accountId,
        private readonly string $clientId,
        private readonly string $clientSecret,
        private readonly string $zoomUserId,
        private readonly string $appTimezone,
    ) {
    }

    /**
     * @return array{
     *     meeting_id: string,
     *     join_url: string,
     *     start_url: string,
     *     password: string,
     * }
     */
    public function createMeeting(
        string $topic,
        \DateTimeInterface $start,
        int $durationMinutes,
        string $agenda = '',
    ): array {
        $token = $this->getAccessToken();

        $tz = new \DateTimeZone($this->resolveTimezone());
        $localStart = (new \DateTimeImmutable('@'.$start->getTimestamp()))->setTimezone($tz);

        $payload = [
            'topic' => $topic,
            'type' => 2,
            'start_time' => $localStart->format('Y-m-d\TH:i:s'),
            'duration' => max(1, $durationMinutes),
            'timezone' => $this->resolveTimezone(),
            'agenda' => $agenda,
            'settings' => [
                'host_video' => true,
                'participant_video' => true,
                'join_before_host' => false,
                'waiting_room' => true,
                'auto_recording' => 'none',
            ],
        ];

        $url = self::API_BASE.'/users/'.$this->encodePathSegment($this->zoomUserId).'/meetings';
        $response = $this->httpClient->request('POST', $url, [
            'headers' => [
                'Authorization' => 'Bearer '.$token,
                'Content-Type' => 'application/json',
            ],
            'json' => $payload,
        ]);

        $data = $this->decodeJsonResponse($response, 'Zoom create meeting');

        return [
            'meeting_id' => (string) $data['id'],
            'join_url' => (string) ($data['join_url'] ?? ''),
            'start_url' => (string) ($data['start_url'] ?? ''),
            'password' => (string) ($data['password'] ?? ''),
        ];
    }

    public function deleteMeeting(string $meetingId): void
    {
        if (!$meetingId || $meetingId === '0') {
            return;
        }

        $token = $this->getAccessToken();
        $url = self::API_BASE.'/meetings/'.$this->encodePathSegment($meetingId);

        try {
            $response = $this->httpClient->request('DELETE', $url, [
                'headers' => ['Authorization' => 'Bearer '.$token],
            ]);
        } catch (\Throwable $e) {
            $this->logger->warning('Zoom delete meeting request failed', [
                'meeting_id' => $meetingId,
                'exception' => $e->getMessage(),
            ]);

            return;
        }

        $code = $response->getStatusCode();
        if ($code === 404) {
            $this->logger->info('Zoom meeting already absent (404)', ['meeting_id' => $meetingId]);

            return;
        }
        if ($code >= 400) {
            $this->logger->warning('Zoom delete meeting returned error', [
                'meeting_id' => $meetingId,
                'status' => $code,
                'body' => $response->getContent(false),
            ]);
        }
    }

    public function updateMeeting(
        string $meetingId,
        string $topic,
        \DateTimeInterface $start,
        int $durationMinutes,
        string $agenda = '',
    ): void {
        if (!$meetingId || $meetingId === '0') {
            return;
        }

        $token = $this->getAccessToken();
        $tz = new \DateTimeZone($this->resolveTimezone());
        $localStart = (new \DateTimeImmutable('@'.$start->getTimestamp()))->setTimezone($tz);

        $payload = [
            'topic' => $topic,
            'start_time' => $localStart->format('Y-m-d\TH:i:s'),
            'duration' => max(1, $durationMinutes),
            'timezone' => $this->resolveTimezone(),
            'agenda' => $agenda,
        ];

        $url = self::API_BASE.'/meetings/'.$this->encodePathSegment($meetingId);
        $response = $this->httpClient->request('PATCH', $url, [
            'headers' => [
                'Authorization' => 'Bearer '.$token,
                'Content-Type' => 'application/json',
            ],
            'json' => $payload,
        ]);

        $this->decodeJsonResponse($response, 'Zoom update meeting');
    }

    private function getAccessToken(): string
    {
        if ($this->clientId === '' || $this->clientSecret === '' || $this->accountId === '') {
            $this->logger->error('Zoom OAuth: missing account credentials (env ZOOM_*)');

            throw new ZoomApiException('Zoom credentials are not configured.');
        }

        return $this->cache->get('zoom_access_token', function (ItemInterface $item): string {
            $item->expiresAfter(3500);

            try {
                $response = $this->httpClient->request('POST', self::TOKEN_URL, [
                    'auth_basic' => [$this->clientId, $this->clientSecret],
                    'headers' => [
                        'Content-Type' => 'application/x-www-form-urlencoded',
                    ],
                    'body' => http_build_query([
                        'grant_type' => 'account_credentials',
                        'account_id' => $this->accountId,
                    ]),
                ]);
            } catch (\Throwable $e) {
                $this->logger->error('Zoom OAuth token request failed', ['exception' => $e->getMessage()]);

                throw new ZoomApiException('Unable to obtain Zoom access token.', 0, $e);
            }

            $data = $this->decodeJsonResponse($response, 'Zoom OAuth token');

            if (!isset($data['access_token']) || !is_string($data['access_token']) || $data['access_token'] === '') {
                $this->logger->error('Zoom OAuth token response missing access_token', ['body_keys' => array_keys($data)]);

                throw new ZoomApiException('Invalid Zoom token response.');
            }

            return $data['access_token'];
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJsonResponse(ResponseInterface $response, string $context): array
    {
        $code = $response->getStatusCode();
        $raw = $response->getContent(false);

        if ($code < 200 || $code >= 300) {
            $this->logger->error('Zoom API HTTP error', [
                'context' => $context,
                'status' => $code,
                'body' => $raw,
            ]);

            throw new ZoomApiException(sprintf('%s failed (HTTP %d).', $context, $code));
        }

        if ($raw === '') {
            return [];
        }

        try {
            /** @var array<string, mixed> $decoded */
            $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            $this->logger->error('Zoom API invalid JSON', ['context' => $context, 'exception' => $e->getMessage()]);

            throw new ZoomApiException(sprintf('%s returned invalid JSON.', $context), 0, $e);
        }

        return $decoded;
    }

    private function encodePathSegment(string $value): string
    {
        return rawurlencode($value);
    }

    private function resolveTimezone(): string
    {
        $tz = $this->appTimezone !== '' ? $this->appTimezone : date_default_timezone_get();

        try {
            new \DateTimeZone($tz);
        } catch (\Exception) {
            $this->logger->warning('Invalid app timezone, falling back to UTC', ['configured' => $tz]);

            return 'UTC';
        }

        return $tz;
    }
}
