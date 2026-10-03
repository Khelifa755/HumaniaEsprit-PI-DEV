<?php

namespace App\Controller\SOCIALMEDIA;

use App\Entity\Users;
use App\Entity\Follow;
use App\Entity\Savedpost;
use App\Entity\Groups;
use App\Entity\Groupmember;
use App\Entity\Userprofile;
use App\Repository\SOCIALMEDIA\SavedpostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

#[Route('/social/network', name: 'social_network_')]
class NetworkController extends AbstractController
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
        private LoggerInterface $logger,
        private SavedpostRepository $savedRepo,
        private \App\Repository\SOCIALMEDIA\NotificationRepository $notifRepo,
        private NotificationController $notificationController,
    ) {}

    /**
     * Network page with tabs: following, followers, pending
     * GET /social/network?tab=following|followers|pending
     */
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $currentUserId = $this->getCurrentUserId();
        $tab = $request->query->get('tab', 'following');
        
        $user = $this->em->getRepository(Users::class)->find($currentUserId);
        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        // Get stats
        $followingCount = $this->countFollowing($currentUserId);
        $followersCount = $this->countFollowers($currentUserId);
        $pendingCount = $this->countPendingFollowers($currentUserId);

        // Get data for current tab
        $items = [];
        switch ($tab) {
            case 'followers':
                $items = $this->getFollowerUsers($currentUserId);
                break;
            case 'pending':
                $items = $this->getPendingFollowerUsers($currentUserId);
                break;
            case 'following':
            default:
                $items = $this->getFollowingUsers($currentUserId);
                break;
        }

        // ── SIDEBAR DATA ──
        $userSavedCount = $this->savedRepo->countByUser($currentUserId);
        $userGroups = $this->em->getRepository(Groups::class)->findAll();
        $userGroupsData = [];
        foreach ($userGroups as $group) {
            $membership = $this->em->getRepository(Groupmember::class)->findOneBy(['groupId' => $group, 'userId' => $user]);
            if ($membership) $userGroupsData[] = $group;
        }
        $userProfile = $this->em->getRepository(Userprofile::class)->findOneBy(['userId' => $user]);

        return $this->render('SOCIALMEDIA/network/index.html.twig', [
            'tab' => $tab,
            'followingCount' => $followingCount,
            'followersCount' => $followersCount,
            'pendingCount' => $pendingCount,
            'items' => $items,
            'currentUserId' => $currentUserId,
            'user' => $user,
            'notifications' => $this->notifRepo->findByUserId($currentUserId, 50),
            'userSavedCount' => $userSavedCount,
            'userGroups' => $userGroupsData,
            'eventsCount' => 0,
            'birthdaysCount' => 0,
            'userProfile' => $userProfile,
        ]);
    }

    /**
     * Follow a user
     * POST /social/network/follow/{id}
     */
    #[Route('/follow/{id}', name: 'follow', methods: ['POST'])]
    public function follow(int $id): JsonResponse
    {
        $currentUserId = null;
        try {
            $currentUserId = $this->getCurrentUserId();
            
            if ($currentUserId === $id) {
                return new JsonResponse(['error' => 'Cannot follow yourself'], 400);
            }

            $currentUser = $this->em->getRepository(Users::class)->find($currentUserId);
            if (!$currentUser) {
                return new JsonResponse(['error' => 'Current user not found'], 404);
            }

            $targetUser = $this->em->getRepository(Users::class)->find($id);
            if (!$targetUser) {
                return new JsonResponse(['error' => 'User not found'], 404);
            }

            $existing = $this->em->getRepository(Follow::class)
                ->createQueryBuilder('f')
                ->where('f.followerId = :followerId')
                ->andWhere('f.followingId = :followingId')
                ->setParameter('followerId', $currentUser)
                ->setParameter('followingId', $targetUser)
                ->getQuery()
                ->getOneOrNullResult();

            if ($existing) {
                return new JsonResponse(['error' => 'Already following'], 400);
            }

            // Create follow (PENDING by default, ACTIVE if current user is manager)
            $follow = new Follow();
            $follow->setFollowerId($currentUser);
            $follow->setFollowingId($targetUser);
            $follow->setFollowedAt(new \DateTime());
            
            $role = $currentUser->getRole();
            $isManager = $role && (strpos($role, 'MANAGER') !== false || strpos($role, 'ADMIN') !== false);
            $follow->setStatus($isManager ? 'ACTIVE' : 'PENDING');

            $this->em->persist($follow);
            $this->em->flush();

            // Send notification
            if ($isManager) {
                // Manager follows automatically - send ACTIVE follow notification
                $this->notificationController->notifyFollow($id, $currentUserId);
            } else {
                // Non-manager - send FOLLOW REQUEST notification
                $this->notificationController->notifyFollowRequest($id, $currentUserId);
            }

            return new JsonResponse(['success' => true, 'status' => $follow->getStatus()], 200);
        } catch (\Throwable $e) {
            $errorMsg = $e->getMessage();
            $this->logger->error('Follow error: ' . $errorMsg, [
                'exception' => $e,
                'userId' => $id,
                'currentUser' => $currentUserId,
            ]);
            return new JsonResponse(['error' => 'Database operation failed: ' . $errorMsg], 500);
        }
    }

    /**
     * Check follow status with a user
     * GET /social/network/check-follow/{id}
     */
    #[Route('/check-follow/{id}', name: 'check_follow', methods: ['GET'])]
    public function checkFollow(int $id): JsonResponse
    {
        try {
            $currentUserId = $this->getCurrentUserId();
            
            $currentUser = $this->em->getRepository(Users::class)->find($currentUserId);
            if (!$currentUser) {
                return new JsonResponse(['error' => 'Current user not found'], 404);
            }

            $targetUser = $this->em->getRepository(Users::class)->find($id);
            if (!$targetUser) {
                return new JsonResponse(['error' => 'User not found'], 404);
            }

            // Check if current user is following this user
            $follow = $this->em->getRepository(Follow::class)
                ->createQueryBuilder('f')
                ->where('f.followerId = :followerId')
                ->andWhere('f.followingId = :followingId')
                ->setParameter('followerId', $currentUser)
                ->setParameter('followingId', $targetUser)
                ->getQuery()
                ->getOneOrNullResult();

            if ($follow) {
                return new JsonResponse([
                    'isFollowing' => true,
                    'status' => $follow->getStatus()
                ], 200);
            } else {
                return new JsonResponse([
                    'isFollowing' => false,
                    'status' => null
                ], 200);
            }
        } catch (\Throwable $e) {
            $this->logger->error('Check follow error: ' . $e->getMessage());
            return new JsonResponse(['error' => 'Server error'], 500);
        }
    }

    /**
     * Unfollow a user
     * POST /social/network/unfollow/{id}
     */
    #[Route('/unfollow/{id}', name: 'unfollow', methods: ['POST'])]
    public function unfollow(int $id): JsonResponse
    {
        $currentUserId = $this->getCurrentUserId();
        $currentUser = $this->em->getRepository(Users::class)->find($currentUserId);
        $targetUser = $this->em->getRepository(Users::class)->find($id);

        $follow = $this->em->getRepository(Follow::class)
            ->createQueryBuilder('f')
            ->where('f.followerId = :followerId')
            ->andWhere('f.followingId = :followingId')
            ->setParameter('followerId', $currentUser)
            ->setParameter('followingId', $targetUser)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$follow) {
            return $this->json(['error' => 'Not following this user'], 404);
        }

        $this->em->remove($follow);
        $this->em->flush();

        return $this->json(['success' => true]);
    }

    /**
     * Accept a follow request
     * POST /social/network/accept/{id}
     */
    #[Route('/accept/{id}', name: 'accept', methods: ['POST'])]
    public function accept(int $id): JsonResponse
    {
        $currentUserId = $this->getCurrentUserId();
        $currentUser = $this->em->getRepository(Users::class)->find($currentUserId);
        $followerUser = $this->em->getRepository(Users::class)->find($id);

        // Find PENDING follow where id is follower and currentUser is following
        $follow = $this->em->getRepository(Follow::class)
            ->createQueryBuilder('f')
            ->where('f.followerId = :followerId')
            ->andWhere('f.followingId = :followingId')
            ->andWhere('f.status = :status')
            ->setParameter('followerId', $followerUser)
            ->setParameter('followingId', $currentUser)
            ->setParameter('status', 'PENDING')
            ->getQuery()
            ->getOneOrNullResult();

        if (!$follow) {
            return $this->json(['error' => 'Follow request not found'], 404);
        }

        $follow->setStatus('ACTIVE');
        $this->em->flush();

        // Send notification to follower that their request was accepted
        $this->notificationController->notifyFollow($id, $currentUserId);

        return $this->json(['success' => true]);
    }

    /**
     * Reject a follow request
     * POST /social/network/reject/{id}
     */
    #[Route('/reject/{id}', name: 'reject', methods: ['POST'])]
    public function reject(int $id): JsonResponse
    {
        $currentUserId = $this->getCurrentUserId();
        $currentUser = $this->em->getRepository(Users::class)->find($currentUserId);
        $followerUser = $this->em->getRepository(Users::class)->find($id);

        $follow = $this->em->getRepository(Follow::class)
            ->createQueryBuilder('f')
            ->where('f.followerId = :followerId')
            ->andWhere('f.followingId = :followingId')
            ->andWhere('f.status = :status')
            ->setParameter('followerId', $followerUser)
            ->setParameter('followingId', $currentUser)
            ->setParameter('status', 'PENDING')
            ->getQuery()
            ->getOneOrNullResult();

        if (!$follow) {
            return $this->json(['error' => 'Follow request not found'], 404);
        }

        $this->em->remove($follow);
        $this->em->flush();

        return $this->json(['success' => true]);
    }

    /**
     * Remove a follower
     * POST /social/network/remove/{id}
     */
    #[Route('/remove/{id}', name: 'remove', methods: ['POST'])]
    public function removeFollower(int $id): JsonResponse
    {
        $currentUserId = $this->getCurrentUserId();
        $currentUser = $this->em->getRepository(Users::class)->find($currentUserId);
        $followerUser = $this->em->getRepository(Users::class)->find($id);

        $follow = $this->em->getRepository(Follow::class)
            ->createQueryBuilder('f')
            ->where('f.followerId = :followerId')
            ->andWhere('f.followingId = :followingId')
            ->setParameter('followerId', $followerUser)
            ->setParameter('followingId', $currentUser)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$follow) {
            return $this->json(['error' => 'Follower not found'], 404);
        }

        $this->em->remove($follow);
        $this->em->flush();

        return $this->json(['success' => true]);
    }

    // ===== HELPERS =====

    private function countFollowing(int $userId): int
    {
        return $this->em->getRepository(Follow::class)
            ->createQueryBuilder('f')
            ->select('COUNT(f.id)')
            ->where('f.followerId = :userId')
            ->andWhere('f.status = :status')
            ->setParameter('userId', $userId)
            ->setParameter('status', 'ACTIVE')
            ->getQuery()
            ->getSingleScalarResult();
    }

    private function countFollowers(int $userId): int
    {
        return $this->em->getRepository(Follow::class)
            ->createQueryBuilder('f')
            ->select('COUNT(f.id)')
            ->where('f.followingId = :userId')
            ->andWhere('f.status = :status')
            ->setParameter('userId', $userId)
            ->setParameter('status', 'ACTIVE')
            ->getQuery()
            ->getSingleScalarResult();
    }

    private function countPendingFollowers(int $userId): int
    {
        return $this->em->getRepository(Follow::class)
            ->createQueryBuilder('f')
            ->select('COUNT(f.id)')
            ->where('f.followingId = :userId')
            ->andWhere('f.status = :status')
            ->setParameter('userId', $userId)
            ->setParameter('status', 'PENDING')
            ->getQuery()
            ->getSingleScalarResult();
    }

    private function getFollowingUsers(int $userId): array
    {
        $follows = $this->em->getRepository(Follow::class)
            ->createQueryBuilder('f')
            ->where('f.followerId = :userId')
            ->andWhere('f.status = :status')
            ->setParameter('userId', $userId)
            ->setParameter('status', 'ACTIVE')
            ->getQuery()
            ->getResult();

        $result = [];
        foreach ($follows as $follow) {
            $targetUser = $follow->getFollowingId();
            // Exclude current user from the list
            if ($targetUser && $targetUser->getId() !== $userId) {
                $result[] = [
                    'user' => $targetUser,
                    'type' => 'FOLLOWING',
                    'isMutual' => $this->isActiveFollowing($targetUser->getId(), $userId),
                    'status' => 'ACTIVE',
                ];
            }
        }
        return $result;
    }

    private function getFollowerUsers(int $userId): array
    {
        $follows = $this->em->getRepository(Follow::class)
            ->createQueryBuilder('f')
            ->where('f.followingId = :userId')
            ->andWhere('f.status = :status')
            ->setParameter('userId', $userId)
            ->setParameter('status', 'ACTIVE')
            ->getQuery()
            ->getResult();

        $result = [];
        foreach ($follows as $follow) {
            $followerUser = $follow->getFollowerId();
            // Exclude current user from the list
            if ($followerUser && $followerUser->getId() !== $userId) {
                $result[] = [
                    'user' => $followerUser,
                    'type' => 'FOLLOWER',
                    'isMutual' => $this->isActiveFollowing($userId, $followerUser->getId()),
                    'status' => 'ACTIVE',
                ];
            }
        }
        return $result;
    }

    private function getPendingFollowerUsers(int $userId): array
    {
        $follows = $this->em->getRepository(Follow::class)
            ->createQueryBuilder('f')
            ->where('f.followingId = :userId')
            ->andWhere('f.status = :status')
            ->setParameter('userId', $userId)
            ->setParameter('status', 'PENDING')
            ->getQuery()
            ->getResult();

        $result = [];
        foreach ($follows as $follow) {
            $followerUser = $follow->getFollowerId();
            // Exclude current user from the list
            if ($followerUser && $followerUser->getId() !== $userId) {
                $result[] = [
                    'user' => $followerUser,
                    'type' => 'PENDING',
                    'isMutual' => false,
                    'status' => 'PENDING',
                ];
            }
        }
        return $result;
    }

    private function isActiveFollowing(int $followerId, int $followingId): bool
    {
        $result = $this->em->getRepository(Follow::class)
            ->createQueryBuilder('f')
            ->select('COUNT(f.id)')
            ->where('f.followerId = :followerId')
            ->andWhere('f.followingId = :followingId')
            ->andWhere('f.status = :status')
            ->setParameter('followerId', $followerId)
            ->setParameter('followingId', $followingId)
            ->setParameter('status', 'ACTIVE')
            ->getQuery()
            ->getSingleScalarResult();
        return (bool) $result;
    }
}
