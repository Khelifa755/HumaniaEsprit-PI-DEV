<?php

namespace App\Controller\SOCIALMEDIA;

use App\Entity\Publication;
use App\Entity\Commentaire;
use App\Entity\Users;
use App\Entity\Groups;
use App\Entity\Groupmember;
use App\Entity\Follow;
use App\Repository\SOCIALMEDIA\SavedpostRepository;
use App\Repository\SOCIALMEDIA\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin', name: 'admin_')]
class AdminController extends AbstractController {
    
    private function getCurrentUserId(): int {
        $user = $this->getUser();
        if (!$user || !method_exists($user, 'getId') || $user->getId() === null) {
            throw $this->createAccessDeniedException('Utilisateur non authentifie');
        }

        return (int) $user->getId();
    }

    public function __construct(
        private EntityManagerInterface $em,
        private SavedpostRepository $savedRepo,
        private NotificationRepository $notifRepo
    ) {}

    /**
     * Admin Dashboard - Show all stats
     */
    #[Route('/dashboard', name: 'dashboard', methods: ['GET'])]
    public function dashboard(): Response {
        $currentUserId = $this->getCurrentUserId();
        // Check if user is ADMIN
        $currentUser = $this->em->getRepository(Users::class)->find($currentUserId);
        if (!$currentUser || $currentUser->getRole() !== 'ADMIN') {
            throw $this->createAccessDeniedException('Admin access required');
        }

        // Sidebar data
        $userSavedCount = count($this->savedRepo->findByUser($currentUserId));
        
        // Followers count
        $qb = $this->em->createQueryBuilder();
        $followersCount = $qb
            ->select('COUNT(f.id)')
            ->from(Follow::class, 'f')
            ->where('f.followingId = :userId')
            ->andWhere("f.status = 'ACTIVE'")
            ->setParameter('userId', $currentUserId)
            ->getQuery()
            ->getSingleScalarResult();
        
        // Following count
        $qb = $this->em->createQueryBuilder();
        $followingCount = $qb
            ->select('COUNT(f.id)')
            ->from(Follow::class, 'f')
            ->where('f.followerId = :userId')
            ->andWhere("f.status = 'ACTIVE'")
            ->setParameter('userId', $currentUserId)
            ->getQuery()
            ->getSingleScalarResult();
        
        $userProfile = $this->em->getRepository(\App\Entity\Userprofile::class)->findOneBy([
            'userId' => $currentUser
        ]);
        
        $userGroups = $this->em->getRepository(Groupmember::class)->findBy([
            'userId' => $currentUser
        ]);
        $userGroupsData = [];
        foreach ($userGroups as $membership) {
            $userGroupsData[] = $membership->getGroupId();
        }

        return $this->render('SOCIALMEDIA/admin/dashboard.html.twig', [
            'currentUser' => $currentUser,
            'currentUserId' => $currentUserId,
            'notifications' => $this->notifRepo->findByUserId($currentUserId, 50),
            'userSavedCount' => $userSavedCount,
            'followersCount' => $followersCount,
            'followingCount' => $followingCount,
            'eventsCount' => 0,
            'birthdaysCount' => 0,
            'userProfile' => $userProfile,
            'userGroups' => $userGroupsData,
        ]);
    }

    /**
     * API endpoint for stats - returns JSON
     */
    #[Route('/api/stats', name: 'api_stats', methods: ['GET'])]
    public function getStats(): JsonResponse {
        // Check if user is ADMIN
        $currentUser = $this->em->getRepository(Users::class)->find($this->getCurrentUserId());
        if (!$currentUser || $currentUser->getRole() !== 'ADMIN') {
            return $this->json(['error' => 'Admin access required'], 403);
        }

        try {
            // Total stats using QueryBuilder to properly count
            $totalUsers = $this->em->getRepository(Users::class)->count([]);
            
            // Total Publications (all, not just PUBLIE)
            $qb = $this->em->createQueryBuilder();
            $totalPublications = $qb
                ->select('COUNT(p.id)')
                ->from(Publication::class, 'p')
                ->getQuery()
                ->getSingleScalarResult();
            
            // Total Comments (all, not just PUBLIE)
            $qb = $this->em->createQueryBuilder();
            $totalComments = $qb
                ->select('COUNT(c.id)')
                ->from(Commentaire::class, 'c')
                ->getQuery()
                ->getSingleScalarResult();
            
            $totalGroups = $this->em->getRepository(Groups::class)->count([]);

            // Weekly trend - last 7 days
            $sevenDaysAgo = new \DateTime();
            $sevenDaysAgo->modify('-7 days');
            
            $qb = $this->em->createQueryBuilder();
            $weeklyNewUsers = $qb
                ->select('COUNT(u.id)')
                ->from(Users::class, 'u')
                ->where('u.createdAt >= :weekAgo')
                ->setParameter('weekAgo', $sevenDaysAgo)
                ->getQuery()
                ->getSingleScalarResult();
            
            $qb = $this->em->createQueryBuilder();
            $weeklyNewPublications = $qb
                ->select('COUNT(p.id)')
                ->from(Publication::class, 'p')
                ->where('p.dateCreation >= :weekAgo')
                ->setParameter('weekAgo', $sevenDaysAgo)
                ->getQuery()
                ->getSingleScalarResult();
            
            $qb = $this->em->createQueryBuilder();
            $weeklyNewComments = $qb
                ->select('COUNT(c.id)')
                ->from(Commentaire::class, 'c')
                ->where('c.dateCreation >= :weekAgo')
                ->setParameter('weekAgo', $sevenDaysAgo)
                ->getQuery()
                ->getSingleScalarResult();
            
            $qb = $this->em->createQueryBuilder();
            $weeklyNewGroups = $qb
                ->select('COUNT(g.id)')
                ->from(Groups::class, 'g')
                ->where('g.createdAt >= :weekAgo')
                ->setParameter('weekAgo', $sevenDaysAgo)
                ->getQuery()
                ->getSingleScalarResult();
            
            $weeklyTrend = [
                'users' => $weeklyNewUsers,
                'publications' => $weeklyNewPublications,
                'comments' => $weeklyNewComments,
                'groups' => $weeklyNewGroups
            ];
            $groupStats = [];
            $groups = $this->em->getRepository(Groups::class)->findAll();
            foreach ($groups as $group) {
                $qb = $this->em->createQueryBuilder();
                $pubCount = $qb
                    ->select('COUNT(p.id)')
                    ->from(Publication::class, 'p')
                    ->where('p.groupId = :groupId')
                    ->setParameter('groupId', $group)
                    ->getQuery()
                    ->getSingleScalarResult();
                
                // Get comment count for publications in this group
                $qb = $this->em->createQueryBuilder();
                $commentCount = $qb
                    ->select('COUNT(c.id)')
                    ->from(Commentaire::class, 'c')
                    ->innerJoin(Publication::class, 'p', 'WITH', 'c.publicationId = p.id')
                    ->where('p.groupId = :groupId')
                    ->setParameter('groupId', $group)
                    ->getQuery()
                    ->getSingleScalarResult();
                
                $engagementRate = $pubCount > 0 ? ($commentCount / $pubCount) * 100 : 0;
                
                $memberCount = $this->em->getRepository(Groupmember::class)->count(['groupId' => $group]);
                $groupStats[] = [
                    'id' => $group->getId(),
                    'name' => $group->getName(),
                    'publications' => $pubCount,
                    'comments' => $commentCount,
                    'members' => $memberCount,
                    'engagementRate' => round($engagementRate, 1)
                ];
            }

            // Top contributors (users with most publications) - COUNT ALL publications
            $qb = $this->em->createQueryBuilder();
            $topContributors = $qb
                ->select('u.id, u.firstName, u.lastName, COUNT(p.id) as pubCount')
                ->from(Users::class, 'u')
                ->leftJoin(Publication::class, 'p', 'WITH', 'p.authorId = u.id')
                ->groupBy('u.id')
                ->orderBy('pubCount', 'DESC')
                ->setMaxResults(5)
                ->getQuery()
                ->getResult();

            // Posts per user - COUNT ALL publications
            $postsPerUser = [];
            $users = $this->em->getRepository(Users::class)->findAll();
            foreach ($users as $user) {
                $qb = $this->em->createQueryBuilder();
                $pubCount = $qb
                    ->select('COUNT(p.id)')
                    ->from(Publication::class, 'p')
                    ->where('p.authorId = :userId')
                    ->setParameter('userId', $user)
                    ->getQuery()
                    ->getSingleScalarResult();
                
                if ($pubCount > 0) {
                    $postsPerUser[] = [
                        'id' => $user->getId(),
                        'name' => $user->getFirstName() . ' ' . $user->getLastName(),
                        'posts' => $pubCount
                    ];
                }
            }
            // Sort by posts descending and limit to 10
            usort($postsPerUser, function($a, $b) {
                return $b['posts'] - $a['posts'];
            });
            $postsPerUser = array_slice($postsPerUser, 0, 10);

            // Recent users - get last 10 users by creation date with role
            $qb = $this->em->createQueryBuilder();
            $recentUsers = $qb
                ->select('u.id, u.firstName, u.lastName, u.createdAt, u.role')
                ->from(Users::class, 'u')
                ->orderBy('u.createdAt', 'DESC')
                ->setMaxResults(10)
                ->getQuery()
                ->getResult();
            
            // Format dates for display
            $recentUsersFormatted = [];
            foreach ($recentUsers as $user) {
                $role = $user['role'] === 'ADMIN' ? 'Admin' : 'Membre';
                $recentUsersFormatted[] = [
                    'id' => $user['id'],
                    'name' => $user['firstName'] . ' ' . $user['lastName'],
                    'joinDate' => $user['createdAt'] ? $user['createdAt']->format('d/m/Y') : 'N/A',
                    'role' => $role
                ];
            }

            // Inactive users - users with 0 publications
            $allUsers = $this->em->getRepository(Users::class)->findAll();
            $inactiveUsers = [];
            $activeUserIds = array_map(fn($u) => $u['id'], $recentUsersFormatted);
            
            foreach ($allUsers as $user) {
                $qb = $this->em->createQueryBuilder();
                $pubCount = $qb
                    ->select('COUNT(p.id)')
                    ->from(Publication::class, 'p')
                    ->where('p.authorId = :userId')
                    ->setParameter('userId', $user)
                    ->getQuery()
                    ->getSingleScalarResult();
                
                if ($pubCount == 0) {
                    $inactiveUsers[] = [
                        'id' => $user->getId(),
                        'name' => $user->getFirstName() . ' ' . $user->getLastName()
                    ];
                }
            }
            // Limit to 10
            $inactiveUsers = array_slice($inactiveUsers, 0, 10);

            // Activity data - publications & new users by day for last 90 days
            $activityData = [];
            $today = new \DateTime();
            for ($i = 89; $i >= 0; $i--) {
                $date = new \DateTime();
                $date->modify("-$i days");
                $dateStart = clone $date;
                $dateEnd = clone $date;
                $dateEnd->modify('+1 day');
                $displayDate = $date->format('M d');
                
                $qb = $this->em->createQueryBuilder();
                $pubsCount = $qb
                    ->select('COUNT(p.id)')
                    ->from(Publication::class, 'p')
                    ->where('p.dateCreation >= :startDate AND p.dateCreation < :endDate')
                    ->setParameter('startDate', $dateStart)
                    ->setParameter('endDate', $dateEnd)
                    ->getQuery()
                    ->getSingleScalarResult();
                
                $qb = $this->em->createQueryBuilder();
                $newUsersCount = $qb
                    ->select('COUNT(u.id)')
                    ->from(Users::class, 'u')
                    ->where('u.createdAt >= :startDate AND u.createdAt < :endDate')
                    ->setParameter('startDate', $dateStart)
                    ->setParameter('endDate', $dateEnd)
                    ->getQuery()
                    ->getSingleScalarResult();
                
                $activityData[] = [
                    'date' => $displayDate,
                    'publications' => $pubsCount,
                    'newUsers' => $newUsersCount
                ];
            }

            return $this->json([
                'summary' => [
                    'totalUsers' => $totalUsers,
                    'totalPublications' => $totalPublications,
                    'totalComments' => $totalComments,
                    'totalGroups' => $totalGroups
                ],
                'weeklyTrend' => $weeklyTrend,
                'groupStats' => $groupStats,
                'topContributors' => $topContributors,
                'recentUsers' => $recentUsersFormatted,
                'inactiveUsers' => $inactiveUsers,
                'activityData' => $activityData
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }
}
