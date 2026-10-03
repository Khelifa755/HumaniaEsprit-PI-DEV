<?php

namespace App\Controller\SOCIALMEDIA;

use App\Entity\Notification;
use App\Entity\Users;
use App\Entity\Publication;
use App\Entity\Commentaire;
use App\Repository\SOCIALMEDIA\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/social/notifications', name: 'social_notifications_')]
class NotificationController extends AbstractController
{
    private function getCurrentUserId(): int
    {
        $user = $this->getUser();
        if (!$user || !method_exists($user, 'getId') || $user->getId() === null) {
            throw $this->createAccessDeniedException('Utilisateur non authentifie');
        }

        return (int) $user->getId();
    }

    public function __construct(
        private EntityManagerInterface $em,
        private NotificationRepository $notifRepo
    ) {}

    /**
     * Obtenir le nombre de notifications non lues
     * GET /social/notifications/unread-count
     */
    #[Route('/unread-count', name: 'unread_count', methods: ['GET'])]
    public function unreadCount(): JsonResponse
    {
        $currentUserId = $this->getCurrentUserId();
        $count = $this->notifRepo->countUnread($currentUserId);

        return $this->json(['count' => $count]);
    }

    /**
     * Lister les notifications de l'utilisateur
     * GET /social/notifications/list
     */
    #[Route('/list', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        try {
            $currentUserId = $this->getCurrentUserId();
            $notifications = $this->notifRepo->findByUserId($currentUserId, 50);

            $data = [];
            foreach ($notifications as $notif) {
                // Get related user info if available
                $relatedUser = $notif->getRelatedUserId();
                $userName = $relatedUser ? 
                    ($relatedUser->getFirstName() . ' ' . $relatedUser->getLastName()) : 
                    'Quelqu\'un';
                
                // Special handling for GROUP_MEMBER_ADDED
                if ($notif->getType() === 'GROUP_MEMBER_ADDED') {
                    $groupId = $notif->getRelatedPublicationId();
                    $group = $this->em->getRepository(\App\Entity\Groups::class)->find($groupId);
                    $groupName = $group ? $group->getName() : 'groupe inconnu';
                    
                    $titre = $notif->getTitre() ?? '👥 Ajouté à un groupe';
                    $message = $userName . ' vous ajoute dans le groupe ' . $groupName;
                } else {
                    // Replace "Quelqu'un" in titre and message with actual user name
                    $titre = str_replace('Quelqu\'un', $userName, $notif->getTitre() ?? 'Notification');
                    $message = str_replace('Quelqu\'un', $userName, $notif->getMessage() ?? '');
                }
                
                $data[] = [
                    'id' => $notif->getId() ?? 0,
                    'titre' => $titre,
                    'message' => $message,
                    'type' => $notif->getType() ?? 'DEFAULT',
                    'seen' => $notif->getSeen() ?? false,
                    'dateCreation' => $notif->getDateCreation() ? $notif->getDateCreation()->format('c') : date('c'),
                    'icon' => $this->getNotificationIcon($notif->getType() ?? 'DEFAULT'),
                    'relatedUserId' => $relatedUser ? $relatedUser->getId() : null,
                    'relatedUserName' => $userName,
                ];
            }

            return $this->json($data);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Marquer une notification comme lue
     * POST /social/notifications/mark-read/{id}
     */
    #[Route('/mark-read/{id}', name: 'mark_read', methods: ['POST'])]
    public function markRead(int $id): JsonResponse
    {
        $notif = $this->notifRepo->find($id);
        if (!$notif) {
            return $this->json(['error' => 'Notification not found'], 404);
        }

        $this->notifRepo->markAsRead($notif);
        return $this->json(['success' => true]);
    }

    /**
     * Marquer toutes les notifications comme lues
     * POST /social/notifications/mark-all-read
     */
    #[Route('/mark-all-read', name: 'mark_all_read', methods: ['POST'])]
    public function markAllRead(): JsonResponse
    {
        $currentUserId = $this->getCurrentUserId();
        $this->notifRepo->markAllAsRead($currentUserId);
        return $this->json(['success' => true]);
    }

    /**
     * Server-Sent Events (SSE) - WebSocket-like streaming
     * GET /social/notifications/stream
     */
    #[Route('/stream', name: 'stream', methods: ['GET'])]
    public function streamNotifications(): Response
    {
        $currentUserId = $this->getCurrentUserId();
        
        // Headers for SSE
        $response = new Response();
        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('Connection', 'keep-alive');
        $response->headers->set('X-Accel-Buffering', 'no');

        // Initial unread count
        $count = $this->notifRepo->countUnread($currentUserId);
        $response->setContent("data: " . json_encode(['count' => $count]) . "\n\n");

        $response->send();
        return $response;
    }

    // ════════════════════════════════════════════════════════════
    // NOTIFICATION CREATION METHODS
    // ════════════════════════════════════════════════════════════

    /**
     * Créer une notification de LIKE/REACTION
     */
    public function notifyReaction(int $targetUserId, int $reactorUserId, int $publicationId): void
    {
        if ($targetUserId === $reactorUserId) return; // Pas auto-notif

        $this->notifRepo->createNotification(
            userId: $targetUserId,
            type: 'LIKE',
            titre: '👍 Quelqu\'un a aimé votre publication',
            message: 'Une nouvelle réaction sur votre publication',
            relatedUserId: $reactorUserId,
            relatedPublicationId: $publicationId
        );
    }

    /**
     * Créer une notification de COMMENT
     */
    public function notifyComment(int $targetUserId, int $commenterUserId, int $publicationId, string $previewText = ''): void
    {
        if ($targetUserId === $commenterUserId) return; // Pas auto-notif

        $message = $previewText ? substr($previewText, 0, 50) . '...' : 'Un nouveau commentaire sur votre publication';

        $this->notifRepo->createNotification(
            userId: $targetUserId,
            type: 'COMMENT',
            titre: '💬 Nouveau commentaire',
            message: $message,
            relatedUserId: $commenterUserId,
            relatedPublicationId: $publicationId
        );
    }

    /**
     * Créer une notification de SHARE
     */
    public function notifyShare(int $targetUserId, int $sharerUserId, int $publicationId): void
    {
        if ($targetUserId === $sharerUserId) return; // Pas auto-notif

        $this->notifRepo->createNotification(
            userId: $targetUserId,
            type: 'SHARE',
            titre: '📤 Votre publication a été partagée',
            message: 'Quelqu\'un a partagé votre publication',
            relatedUserId: $sharerUserId,
            relatedPublicationId: $publicationId
        );
    }

    /**
     * Créer une notification de FOLLOW
     */
    public function notifyFollow(int $targetUserId, int $followerUserId): void
    {
        if ($targetUserId === $followerUserId) return;

        $this->notifRepo->createNotification(
            userId: $targetUserId,
            type: 'FOLLOW',
            titre: '👤 Quelqu\'un vous suit',
            message: 'Vous avez un nouveau follower',
            relatedUserId: $followerUserId
        );
    }

    /**
     * Créer une notification de FOLLOW REQUEST (PENDING)
     */
    public function notifyFollowRequest(int $targetUserId, int $requesterUserId): void
    {
        if ($targetUserId === $requesterUserId) return;

        $this->notifRepo->createNotification(
            userId: $targetUserId,
            type: 'FOLLOW_REQUEST',
            titre: '❓ Demande de suivi en attente',
            message: 'Quelqu\'un demande à vous suivre',
            relatedUserId: $requesterUserId
        );
    }

    /**
     * Créer une notification de GROUP MEMBER ADDED
     */
    public function notifyGroupMemberAdded(int $targetUserId, int $adminUserId, string $groupName): void
    {
        if ($targetUserId === $adminUserId) return;

        $this->notifRepo->createNotification(
            userId: $targetUserId,
            type: 'GROUP_MEMBER_ADDED',
            titre: '👥 Ajouté à un groupe',
            message: 'Quelqu\'un vous ajoute dans le groupe ' . $groupName,
            relatedUserId: $adminUserId
        );
    }

    /**
     * Helper - obtenir l'icône pour un type de notification
     */
    private function getNotificationIcon(string $type): string
    {
        return match ($type) {
            'LIKE', 'REACTION' => '👍',
            'COMMENT' => '💬',
            'SHARE' => '📤',
            'FOLLOW' => '👤',
            'FOLLOW_REQUEST' => '❓',
            'FOLLOW_ACCEPTED' => '✅',
            'MENTION' => '📢',
            'GROUP' => '👥',
            default => '🔔',
        };
    }
}
