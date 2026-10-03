<?php

namespace App\Controller\SOCIALMEDIA;

use App\Entity\Follow;
use App\Entity\Users;
use App\Repository\SOCIALMEDIA\MessageService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/social/messages', name: 'social_messages_')]
class MessageController extends AbstractController
{
    private function getCurrentUserId(): int
    {
        $user = $this->getUser();
        if (!$user || !method_exists($user, 'getId') || $user->getId() === null) {
            throw $this->createAccessDeniedException('Utilisateur non authentifié');
        }
        return (int) $user->getId();
    }

    public function __construct(
        private MessageService $messageService,
        private EntityManagerInterface $em
    ) {}

    /**
     * Send Private Message
     * POST /social/messages/private
     */
    #[Route('/private', name: 'send_private', methods: ['POST'])]
    public function sendPrivateMessage(Request $request): JsonResponse
    {
        $currentUserId = $this->getCurrentUserId();
        $data = json_decode($request->getContent(), true);

        $content = $data['content'] ?? '';
        $receiverId = $data['receiver_id'] ?? null;
        $senderName = $data['sender_name'] ?? '';
        $senderAvatar = $data['sender_avatar'] ?? '';

        if (!$content || !$receiverId) {
            return $this->json(['error' => 'Content and receiver_id required'], 400);
        }

        $success = $this->messageService->sendPrivateMessage(
            $content,
            $currentUserId,
            $senderName,
            $senderAvatar,
            (int) $receiverId
        );

        if (!$success) {
            return $this->json(['error' => 'Failed to send message'], 500);
        }

        return $this->json(['success' => true, 'message' => 'Message sent']);
    }

    /**
     * Send Group Message
     * POST /social/messages/group
     */
    #[Route('/group', name: 'send_group', methods: ['POST'])]
    public function sendGroupMessage(Request $request): JsonResponse
    {
        $currentUserId = $this->getCurrentUserId();
        $data = json_decode($request->getContent(), true);

        $content = $data['content'] ?? '';
        $groupId = $data['group_id'] ?? null;
        $senderName = $data['sender_name'] ?? '';
        $senderAvatar = $data['sender_avatar'] ?? '';

        if (!$content || !$groupId) {
            return $this->json(['error' => 'Content and group_id required'], 400);
        }

        $success = $this->messageService->sendGroupMessage(
            $content,
            $currentUserId,
            $senderName,
            $senderAvatar,
            (int) $groupId
        );

        if (!$success) {
            return $this->json(['error' => 'Failed to send message'], 500);
        }

        return $this->json(['success' => true, 'message' => 'Message sent']);
    }

    /**
     * Get Private Messages
     * GET /social/messages/private/{otherId}
     */
    #[Route('/private/{otherId}', name: 'get_private', methods: ['GET'])]
    public function getPrivateMessages(int $otherId): JsonResponse
    {
        $currentUserId = $this->getCurrentUserId();
        $messages = $this->messageService->getPrivateMessages($currentUserId, $otherId);

        return $this->json(['messages' => $messages]);
    }

    /**
     * Get Group Messages
     * GET /social/messages/group/{groupId}
     */
    #[Route('/group/{groupId}', name: 'get_group', methods: ['GET'])]
    public function getGroupMessages(int $groupId): JsonResponse
    {
        $messages = $this->messageService->getGroupMessages($groupId);

        return $this->json(['messages' => $messages]);
    }

    /**
     * Count Unread Messages
     * GET /social/messages/unread/count
     */
    #[Route('/unread/count', name: 'unread_count', methods: ['GET'])]
    public function countUnread(): JsonResponse
    {
        $currentUserId = $this->getCurrentUserId();
        $count = $this->messageService->countUnread($currentUserId);

        return $this->json(['unread_count' => $count]);
    }

    /**
     * Count Unread from Specific Sender
     * GET /social/messages/unread/{senderId}
     */
    #[Route('/unread/{senderId}', name: 'unread_from_sender', methods: ['GET'])]
    public function countUnreadFromSender(int $senderId): JsonResponse
    {
        $currentUserId = $this->getCurrentUserId();
        $count = $this->messageService->countUnreadFromSender($senderId, $currentUserId);

        return $this->json(['unread_count' => $count]);
    }

    /**
     * Mark Messages as Read
     * PATCH /social/messages/mark-read/{senderId}
     */
    #[Route('/mark-read/{senderId}', name: 'mark_read', methods: ['PATCH'])]
    public function markAsRead(int $senderId): JsonResponse
    {
        $currentUserId = $this->getCurrentUserId();
        $success = $this->messageService->markAsRead($senderId, $currentUserId);

        if (!$success) {
            return $this->json(['error' => 'Failed to mark as read'], 500);
        }

        return $this->json(['success' => true, 'message' => 'Marked as read']);
    }

    /**
     * Delete Message
     * DELETE /social/messages/{messageId}
     */
    #[Route('/{messageId}', name: 'delete', methods: ['DELETE'])]
    public function deleteMessage(string $messageId): JsonResponse
    {
        $success = $this->messageService->deleteMessage($messageId);

        if (!$success) {
            return $this->json(['error' => 'Failed to delete message'], 500);
        }

        return $this->json(['success' => true, 'message' => 'Message deleted']);
    }

    /**
     * Get Conversation Users (with actual messages in Supabase)
     * GET /social/messages/api/users
     */
    #[Route('/api/users', name: 'api_users', methods: ['GET'])]
    public function getUsers(): JsonResponse
    {
        $currentUserId = $this->getCurrentUserId();

        try {
            $me = $this->em->getRepository(Users::class)->find($currentUserId);
            if (!$me) {
                return $this->json(['error' => 'User not found'], 404);
            }

            // Everyone in the social graph (active follow in either direction)
            $networkIds = $this->getActiveNetworkUserIds($me, $currentUserId);

            $messages = $this->messageService->getConversationUsers($currentUserId);
            usort($messages, fn ($a, $b) => strtotime($b['created_at'] ?? '0') - strtotime($a['created_at'] ?? '0'));

            $fromMessagesIds = [];
            $lastMessages = [];

            foreach ($messages as $msg) {
                if (!empty($msg['group_id'])) {
                    continue;
                }
                if (($msg['receiver_id'] ?? null) === null || ($msg['receiver_id'] ?? '') === '') {
                    continue;
                }
                $senderId = (int) ($msg['sender_id'] ?? 0);
                $receiverId = (int) $msg['receiver_id'];
                $otherId = $senderId === $currentUserId ? $receiverId : $senderId;
                if ($otherId <= 0 || $otherId === $currentUserId) {
                    continue;
                }
                $fromMessagesIds[$otherId] = true;
                if (!isset($lastMessages[$otherId])) {
                    $lastMessages[$otherId] = $msg;
                }
            }

            $allIds = array_unique(array_merge($networkIds, array_keys($fromMessagesIds)));
            $userData = [];

            foreach ($allIds as $userId) {
                $user = $this->em->getRepository(Users::class)->find($userId);
                if (!$user) {
                    continue;
                }
                $lastMsg = $lastMessages[$userId] ?? null;
                $unreadCount = $this->messageService->countUnreadFromSender($userId, $currentUserId);

                $userData[] = [
                    'id' => $user->getId(),
                    'username' => $user->getUsername(),
                    'name' => trim(($user->getFirstName() ?? '') . ' ' . ($user->getLastName() ?? '')) ?: $user->getUsername(),
                    'avatarUrl' => $user->getAvatarUrl(),
                    'lastMessage' => $lastMsg ? $lastMsg['content'] : null,
                    'lastMessageTime' => $lastMsg ? $lastMsg['created_at'] : null,
                    'unreadCount' => $unreadCount > 0 ? $unreadCount : 0,
                ];
            }

            usort($userData, function (array $a, array $b): int {
                $ua = ($a['unreadCount'] ?? 0) > 0 ? 1 : 0;
                $ub = ($b['unreadCount'] ?? 0) > 0 ? 1 : 0;
                if ($ua !== $ub) {
                    return $ub <=> $ua;
                }
                $ta = !empty($a['lastMessageTime']) ? strtotime((string) $a['lastMessageTime']) : 0;
                $tb = !empty($b['lastMessageTime']) ? strtotime((string) $b['lastMessageTime']) : 0;
                if ($ta !== $tb) {
                    return $tb <=> $ta;
                }

                return strcasecmp((string) ($a['name'] ?? ''), (string) ($b['name'] ?? ''));
            });

            return $this->json($userData);
        } catch (\Exception $e) {
            \error_log("Error fetching users: " . $e->getMessage());
            return $this->json(['error' => 'Failed to fetch users'], 500);
        }
    }

    /**
     * Users with an ACTIVE follow link to the current user (following or follower).
     *
     * @return int[]
     */
    private function getActiveNetworkUserIds(Users $me, int $currentUserId): array
    {
        $ids = [];

        $following = $this->em->getRepository(Follow::class)->createQueryBuilder('f')
            ->select('following.id AS networkUserId')
            ->join('f.followingId', 'following')
            ->where('f.followerId = :me')
            ->andWhere('f.status = :st')
            ->setParameter('me', $me)
            ->setParameter('st', 'ACTIVE')
            ->getQuery()
            ->getScalarResult();

        foreach ($following as $row) {
            $id = (int) ($row['networkUserId'] ?? 0);
            if ($id > 0 && $id !== $currentUserId) {
                $ids[$id] = true;
            }
        }

        $followers = $this->em->getRepository(Follow::class)->createQueryBuilder('f')
            ->select('follower.id AS networkUserId')
            ->join('f.followerId', 'follower')
            ->where('f.followingId = :me')
            ->andWhere('f.status = :st')
            ->setParameter('me', $me)
            ->setParameter('st', 'ACTIVE')
            ->getQuery()
            ->getScalarResult();

        foreach ($followers as $row) {
            $id = (int) ($row['networkUserId'] ?? 0);
            if ($id > 0 && $id !== $currentUserId) {
                $ids[$id] = true;
            }
        }

        return array_keys($ids);
    }

    /**
     * Get User Groups (for group chat conversations)
     * GET /social/messages/api/groups
     */
    #[Route('/api/groups', name: 'api_groups', methods: ['GET'])]
    public function getGroups(): JsonResponse
    {
        $currentUserId = $this->getCurrentUserId();
        
        try {
            $groups = $this->em->getRepository(\App\Entity\Groups::class)->findAll();
            
            $groupData = [];
            foreach ($groups as $group) {
                $groupData[] = [
                    'id' => $group->getId(),
                    'name' => $group->getName(),
                    'avatar' => null, // Groups don't have avatars in this schema
                ];
            }
            
            return $this->json($groupData);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Failed to fetch groups'], 500);
        }
    }

    /**
     * Debug: Check if Supabase has messages
     * GET /social/messages/debug/check
     */
    #[Route('/debug/check', name: 'debug_check', methods: ['GET'])]
    public function debugCheck(): JsonResponse
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->json(['error' => 'Forbidden'], 403);
        }

        $currentUserId = $this->getCurrentUserId();
        $messages = $this->messageService->getConversationUsers($currentUserId);

        return $this->json([
            'currentUserId' => $currentUserId,
            'messageCount' => count($messages),
            'sample' => array_slice($messages, 0, 5),
        ]);
    }
}
