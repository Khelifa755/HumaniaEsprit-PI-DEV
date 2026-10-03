<?php

namespace App\Repository\SOCIALMEDIA;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class MessageService
{
    private string $supabaseUrl;
    private string $anonKey;
    private string $messagesTable = 'messages';
    private HttpClientInterface $httpClient;

  public function __construct(
    HttpClientInterface $httpClient,
    string $supabaseUrl,
    string $anonKey
) {
    $this->httpClient = $httpClient;
    $this->supabaseUrl = $supabaseUrl;
    $this->anonKey = $anonKey;
}

    /**
     * ── Send Private Message ──
     */
    public function sendPrivateMessage(
        string $content,
        int $senderId,
        string $senderName,
        string $senderAvatar,
        int $receiverId
    ): bool {
        $body = [
            'content' => $content,
            'sender_id' => $senderId,
            'sender_name' => $senderName,
            'sender_avatar' => $senderAvatar,
            'receiver_id' => $receiverId,
            'group_id' => null,
            'is_read' => false,
            'created_at' => (new \DateTime())->format('Y-m-d H:i:s'),
        ];

        return $this->postMessage($body);
    }

    /**
     * ── Send Group Message ──
     */
    public function sendGroupMessage(
        string $content,
        int $senderId,
        string $senderName,
        string $senderAvatar,
        int $groupId
    ): bool {
        $body = [
            'content' => $content,
            'sender_id' => $senderId,
            'sender_name' => $senderName,
            'sender_avatar' => $senderAvatar,
            'receiver_id' => null,
            'group_id' => $groupId,
            'is_read' => false,
            'created_at' => (new \DateTime())->format('Y-m-d H:i:s'),
        ];

        return $this->postMessage($body);
    }

    /**
     * ── Generic POST message ──
     */
    private function postMessage(array $body): bool
    {
        try {
            $url = $this->supabaseUrl . '/rest/v1/' . $this->messagesTable;
            
            $response = $this->httpClient->request('POST', $url, [
                'headers' => $this->getHeaders(),
                'json' => $body,
            ]);

            return $response->getStatusCode() === 201;
        } catch (\Exception $e) {
            error_log("❌ Send message error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * ── Get Private Messages ──
     */
    public function getPrivateMessages(int $userId, int $otherId, int $limit = 100): array
    {
        try {
            $url = $this->supabaseUrl . '/rest/v1/' . $this->messagesTable;

            $query = "select=*&or=(and(sender_id.eq.{$userId},receiver_id.eq.{$otherId}),and(sender_id.eq.{$otherId},receiver_id.eq.{$userId}))&order=created_at.asc&limit={$limit}";

            $response = $this->httpClient->request('GET', $url . '?' . $query, [
                'headers' => $this->getHeaders(),
            ]);

            $content = $response->getContent(false);
            $decoded = json_decode($content, true);

            error_log("Supabase response: " . $content);

            return is_array($decoded) ? $decoded : [];
        } catch (\Exception $e) {
            error_log("❌ Get private messages error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * ── Get Group Messages ──
     */
    public function getGroupMessages(int $groupId, int $limit = 100): array
    {
        try {
            $url = $this->supabaseUrl . '/rest/v1/' . $this->messagesTable;
            
            $filter = "group_id=eq.{$groupId}";
            $order = "order=created_at.asc";
            $queryLimit = "limit={$limit}";

            $response = $this->httpClient->request('GET', $url . '?' . $filter . '&' . $order . '&' . $queryLimit, [
                'headers' => $this->getHeaders(),
            ]);

            return json_decode($response->getContent(), true) ?? [];
        } catch (\Exception $e) {
            error_log("❌ Get group messages error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * ── Count Unread Messages ──
     */
    public function countUnread(int $receiverId): int
    {
        try {
            $url = $this->supabaseUrl . '/rest/v1/' . $this->messagesTable;
            $filter = "receiver_id=eq.{$receiverId}&is_read=is.false";

            $response = $this->httpClient->request('GET', $url . '?' . $filter, [
                'headers' => array_merge($this->getHeaders(), ['Prefer' => 'count=exact']),
            ]);

            $contentRange = $response->getHeaders()['content-range'][0] ?? '';
            if (preg_match('/\/(\d+)$/', $contentRange, $matches)) {
                return (int) $matches[1];
            }
        } catch (\Exception $e) {
            error_log("❌ Count unread error: " . $e->getMessage());
        }

        return 0;
    }

    /**
     * ── Count Unread from Specific Sender ──
     */
    public function countUnreadFromSender(int $senderId, int $receiverId): int
    {
        try {
            $url = $this->supabaseUrl . '/rest/v1/' . $this->messagesTable;
            $filter = "sender_id=eq.{$senderId}&receiver_id=eq.{$receiverId}&is_read=is.false";

            $response = $this->httpClient->request('GET', $url . '?' . $filter, [
                'headers' => array_merge($this->getHeaders(), ['Prefer' => 'count=exact']),
            ]);

            $contentRange = $response->getHeaders()['content-range'][0] ?? '';
            if (preg_match('/\/(\d+)$/', $contentRange, $matches)) {
                return (int) $matches[1];
            }
        } catch (\Exception $e) {
            error_log("❌ Count unread from sender error: " . $e->getMessage());
        }

        return 0;
    }

    /**
     * ── Mark Messages as Read ──
     */
    public function markAsRead(int $senderId, int $receiverId): bool
    {
        try {
            $url = $this->supabaseUrl . '/rest/v1/' . $this->messagesTable;
            $filter = "sender_id=eq.{$senderId}&receiver_id=eq.{$receiverId}&is_read=is.false";

            $response = $this->httpClient->request('PATCH', $url . '?' . $filter, [
                'headers' => $this->getHeaders(),
                'json' => ['is_read' => true],
            ]);

            return $response->getStatusCode() === 204;
        } catch (\Exception $e) {
            error_log("❌ Mark as read error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * ── Delete Message ──
     */
    public function deleteMessage(string $messageId): bool
    {
        try {
            $url = $this->supabaseUrl . '/rest/v1/' . $this->messagesTable . '?id=eq.' . $messageId;

            $response = $this->httpClient->request('DELETE', $url, [
                'headers' => $this->getHeaders(),
            ]);

            return $response->getStatusCode() === 204;
        } catch (\Exception $e) {
            error_log("❌ Delete message error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * ── Get Supabase Headers ──
     */
    private function getHeaders(): array
    {
        return [
            'apikey' => $this->anonKey,
            'Authorization' => 'Bearer ' . $this->anonKey,
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * ── Get Conversation Users (all users with whom current user has messages) ──
     */
    public function getConversationUsers(int $userId): array
    {
        try {
            $url = $this->supabaseUrl . '/rest/v1/' . $this->messagesTable;
            // Get all messages where current user is sender OR receiver
            $query = "select=*&or=(sender_id.eq.{$userId},receiver_id.eq.{$userId})&limit=5000";
            $response = $this->httpClient->request('GET', $url . '?' . $query, [
                'headers' => $this->getHeaders(),
            ]);
            $content = $response->getContent(false);
            $decoded = json_decode($content, true);
            return is_array($decoded) ? $decoded : [];
        } catch (\Exception $e) {
            error_log("❌ Get conversation users error: " . $e->getMessage());
            return [];
        }
    }
}
