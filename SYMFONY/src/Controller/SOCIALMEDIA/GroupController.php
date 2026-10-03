<?php

namespace App\Controller\SOCIALMEDIA;

use App\Entity\Groups;
use App\Entity\Groupmember;
use App\Entity\Users;
use App\Entity\Publication;
use App\Entity\Commentaire;
use App\Entity\Follow;
use App\Repository\SOCIALMEDIA\CommentaireRepository;
use App\Repository\SOCIALMEDIA\ReactionRepository;
use App\Repository\SOCIALMEDIA\ShareRepository;
use App\Repository\SOCIALMEDIA\SavedpostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

#[Route('/social/groups', name: 'social_groups_')]
class GroupController extends AbstractController
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
        private CommentaireRepository $commentRepo,
        private ReactionRepository $reactionRepo,
        private ShareRepository $shareRepo,
        private SavedpostRepository $savedRepo,
        private \App\Repository\SOCIALMEDIA\NotificationRepository $notifRepo,
        private NotificationController $notificationController,
    ) {}

    /**
     * List all groups
     * GET /social/groups
     */
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        $currentUserId = $this->getCurrentUserId();
        $currentUser = $this->em->getRepository(Users::class)->find($currentUserId);

        $groups = $this->em->getRepository(Groups::class)->findAll();

        $groupsData = [];
        foreach ($groups as $group) {
            $memberCount = $this->em->getRepository(Groupmember::class)->count(['groupId' => $group]);
            $isMember = $this->em->getRepository(Groupmember::class)->findOneBy([
                'groupId' => $group,
                'userId' => $currentUser,
            ]);

            $groupsData[] = [
                'group' => $group,
                'memberCount' => $memberCount,
                'isMember' => $isMember !== null,
            ];
        }

        // ── SIDEBAR DATA ──
        $userSavedCount = $this->savedRepo->countByUser($currentUserId);
        
        // Followers count (people who follow this user) - using QueryBuilder like profile page
        $qb = $this->em->createQueryBuilder();
        $followersCount = $qb
            ->select('COUNT(f.id)')
            ->from(Follow::class, 'f')
            ->where('f.followingId = :userId')
            ->andWhere("f.status = 'ACTIVE'")
            ->setParameter('userId', $currentUserId)
            ->getQuery()
            ->getSingleScalarResult();
        
        // Following count (people this user follows) - using QueryBuilder like profile page
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

        return $this->render('SOCIALMEDIA/feed/groups.html.twig', [
            'groups' => $groupsData,
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
     * View group details and posts
     * GET /social/groups/{id}
     */
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): Response
    {
        $currentUserId = $this->getCurrentUserId();
        $group = $this->em->getRepository(Groups::class)->find($id);

        if (!$group) {
            throw $this->createNotFoundException('Groupe non trouvé');
        }

        $currentUser = $this->em->getRepository(Users::class)->find($currentUserId);
        $membership = $this->em->getRepository(Groupmember::class)->findOneBy([
            'groupId' => $group,
            'userId' => $currentUser,
        ]);
        
        $isPlatformAdmin = $currentUser && $currentUser->getRole() === 'ADMIN';

        // ✅ Permission check: Only members can view group details (or platform admin)
        if (!$membership && !$isPlatformAdmin) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas membre de ce groupe');
        }

        $isMember = $membership !== null;
        $isAdmin = $isMember && $membership->getRole() === 'ADMIN';

        $members = $this->em->getRepository(Groupmember::class)->findBy(['groupId' => $group]);
        $memberCount = count($members);

        $publications = $this->em->createQueryBuilder()
            ->select('p', 'u', 'g', 's', 'sa')
            ->from(Publication::class, 'p')
            ->leftJoin('p.authorId', 'u')->addSelect('u')
            ->leftJoin('p.groupId', 'g')->addSelect('g')
            ->leftJoin('p.sharedFromId', 's')->addSelect('s')
            ->leftJoin('s.authorId', 'sa')->addSelect('sa')
            ->where('p.groupId = :group')
            ->andWhere('p.statut != :suppr')
            ->setParameter('group', $group)
            ->setParameter('suppr', 'SUPPRIME')
            ->orderBy('p.dateCreation', 'DESC')
            ->setMaxResults(20)
            ->getQuery()
            ->getResult();

        // Get list of users the current user is following
        $followedUserIds = [];
        $followedUsers = $this->em->getRepository(Follow::class)->findBy([
            'followerId' => $currentUser,
            'status' => 'ACCEPTED'
        ]);
        foreach ($followedUsers as $follow) {
            $followedUserIds[] = $follow->getFollowingId()->getId();
        }

        // Build members with follow status
        $membersWithStatus = [];
        foreach ($members as $member) {
            $userId = $member->getUserId()->getId();
            if ($userId !== $currentUserId) {
                $membersWithStatus[] = [
                    'user' => $member->getUserId(),
                    'isFollowed' => in_array($userId, $followedUserIds)
                ];
            }
        }

        // Get user's groups for share modal
        $userGroupMemberships = $this->em->getRepository(Groupmember::class)->findBy([
            'userId' => $currentUser
        ]);
        $userGroups = [];
        foreach ($userGroupMemberships as $membership) {
            $userGroups[] = $membership->getGroupId();
        }

        $pubIds = array_map(static fn (Publication $p) => $p->getId(), $publications);

        $commentsByPublication = $pubIds !== []
            ? $this->commentRepo->findActiveByPublicationIdsGrouped($pubIds)
            : [];

        $reactionStats = array_fill_keys($pubIds, []);
        $shareCount = array_fill_keys($pubIds, 0);
        $userReactions = array_fill_keys($pubIds, null);
        $userSaved = array_fill_keys($pubIds, false);
        $userShares = array_fill_keys($pubIds, false);

        if ($pubIds !== []) {
            $reactionStats = $this->reactionRepo->aggregateCountsByPublicationIds($pubIds);
            foreach ($pubIds as $pid) {
                if (!isset($reactionStats[$pid])) {
                    $reactionStats[$pid] = [];
                }
            }

            $shareCount = $this->shareRepo->countGroupedByPublicationIds($pubIds);
            foreach ($pubIds as $pid) {
                if (!isset($shareCount[$pid])) {
                    $shareCount[$pid] = 0;
                }
            }

            $userReactions = $this->reactionRepo->findTypesByUserAndPublicationIds($currentUserId, $pubIds);

            foreach ($this->savedRepo->findPublicationIdsSavedByUser($currentUserId, $pubIds) as $pid) {
                if (array_key_exists($pid, $userSaved)) {
                    $userSaved[$pid] = true;
                }
            }

            foreach ($this->shareRepo->findPublicationIdsSharedByUser($currentUserId, $pubIds) as $pid) {
                if (array_key_exists($pid, $userShares)) {
                    $userShares[$pid] = true;
                }
            }
        }

        // ── SIDEBAR DATA ──
        $userSavedCount = $this->savedRepo->countByUser($currentUserId);
        
        // Followers count (people who follow this user) - using QueryBuilder like profile page
        $qb = $this->em->createQueryBuilder();
        $followersCount = $qb
            ->select('COUNT(f.id)')
            ->from(Follow::class, 'f')
            ->where('f.followingId = :userId')
            ->andWhere("f.status = 'ACTIVE'")
            ->setParameter('userId', $currentUserId)
            ->getQuery()
            ->getSingleScalarResult();
        
        // Following count (people this user follows) - using QueryBuilder like profile page
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

        return $this->render('SOCIALMEDIA/feed/group-detail.html.twig', [
            'group' => $group,
            'posts' => $publications,
            'members' => $members,
            'memberCount' => $memberCount,
            'membersWithStatus' => $membersWithStatus,
            'isMember' => $isMember,
            'isAdmin' => $isAdmin,
            'currentUserId' => $currentUserId,
            'commentsByPublication' => $commentsByPublication,
            'currentUser' => $currentUser,
            'userGroups' => $userGroups,
            'reactionStats' => $reactionStats,
            'shareCount' => $shareCount,
            'userReactions' => $userReactions,
            'userSaved' => $userSaved,
            'userShares' => $userShares,
            'notifications' => $this->notifRepo->findByUserId($currentUserId, 50),
            'userSavedCount' => $userSavedCount,
            'followersCount' => $followersCount,
            'followingCount' => $followingCount,
            'eventsCount' => 0,
            'birthdaysCount' => 0,
            'userProfile' => $userProfile,
        ]);
    }

    /**
     * Create a new group (managers only)
     * POST /social/groups/create
     */
    #[Route('/create', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $currentUserId = $this->getCurrentUserId();
            $currentUser = $this->em->getRepository(Users::class)->find($currentUserId);

            if (!$currentUser) {
                return new JsonResponse(['error' => 'User not found'], 404);
            }

            // ✅ Permission check: Only MANAGER role can create groups
            if ($currentUser->getRole() !== 'MANAGER') {
                return new JsonResponse(['error' => 'Only managers can create groups'], 403);
            }

            $name = $request->request->get('name');
            $description = $request->request->get('description');
            $imageUrl = $request->request->get('imageUrl', '');

            if (!$name || !$description) {
                return new JsonResponse(['error' => 'Name and description are required'], 400);
            }

            $group = new Groups();
            $group->setName($name);
            $group->setDescription($description);
            $group->setCreatedById($currentUser);
            $group->setCreatedAt(new \DateTime());
            $group->setImageUrl($imageUrl);
            $group->setMemberCount(1);

            $this->em->persist($group);
            $this->em->flush();

            $member = new Groupmember();
            $member->setGroupId($group);
            $member->setUserId($currentUser);
            $member->setRole('ADMIN');
            $member->setJoinedAt(new \DateTime());

            $this->em->persist($member);
            $this->em->flush();

            return new JsonResponse([
                'success' => true,
                'groupId' => $group->getId(),
            ], 201);
        } catch (\Throwable $e) {
            $this->logger->error('Group creation error: ' . $e->getMessage());
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Join a group
     * POST /social/groups/{id}/join
     */
    #[Route('/{id}/join', name: 'join', methods: ['POST'])]
    public function join(int $id): JsonResponse
    {
        try {
            $currentUserId = $this->getCurrentUserId();
            $currentUser = $this->em->getRepository(Users::class)->find($currentUserId);
            $group = $this->em->getRepository(Groups::class)->find($id);

            if (!$currentUser || !$group) {
                return new JsonResponse(['error' => 'User or group not found'], 404);
            }

            $existing = $this->em->getRepository(Groupmember::class)->findOneBy([
                'groupId' => $group,
                'userId' => $currentUser,
            ]);

            if ($existing) {
                return new JsonResponse(['error' => 'Already a member'], 400);
            }

            $member = new Groupmember();
            $member->setGroupId($group);
            $member->setUserId($currentUser);
            $member->setRole('MEMBER');
            $member->setJoinedAt(new \DateTime());

            $this->em->persist($member);
            $memberCount = $this->em->getRepository(Groupmember::class)->count(['groupId' => $group]);
            $group->setMemberCount($memberCount + 1);
            $this->em->flush();

            return new JsonResponse(['success' => true], 201);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Leave a group
     * POST /social/groups/{id}/leave
     */
    #[Route('/{id}/leave', name: 'leave', methods: ['POST'])]
    public function leave(int $id): JsonResponse
    {
        try {
            $currentUserId = $this->getCurrentUserId();
            $currentUser = $this->em->getRepository(Users::class)->find($currentUserId);
            $group = $this->em->getRepository(Groups::class)->find($id);

            if (!$currentUser || !$group) {
                return new JsonResponse(['error' => 'User or group not found'], 404);
            }

            $membership = $this->em->getRepository(Groupmember::class)->findOneBy([
                'groupId' => $group,
                'userId' => $currentUser,
            ]);

            if (!$membership) {
                return new JsonResponse(['error' => 'Not a member'], 404);
            }

            if ($membership->getRole() === 'ADMIN') {
                $adminCount = $this->em->getRepository(Groupmember::class)->count([
                    'groupId' => $group,
                    'role' => 'ADMIN',
                ]);

                if ($adminCount === 1) {
                    return new JsonResponse(['error' => 'Cannot leave: you are the only admin'], 400);
                }
            }

            $this->em->remove($membership);
            $memberCount = $this->em->getRepository(Groupmember::class)->count(['groupId' => $group]);
            $group->setMemberCount(max(0, $memberCount - 1));
            $this->em->flush();

            return new JsonResponse(['success' => true]);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get group members
     * GET /social/groups/{id}/members
     */
    #[Route('/{id}/members', name: 'members', methods: ['GET'])]
    public function getMembers(int $id): JsonResponse
    {
        try {
            $group = $this->em->getRepository(Groups::class)->find($id);

            if (!$group) {
                return new JsonResponse(['error' => 'Group not found'], 404);
            }

            $members = $this->em->getRepository(Groupmember::class)->findBy(['groupId' => $group]);

            $data = [];
            foreach ($members as $member) {
                $user = $member->getUserId();
                $data[] = [
                    'id' => $user->getId(),
                    'firstName' => $user->getFirstName(),
                    'lastName' => $user->getLastName(),
                    'username' => $user->getUsername(),
                    'avatarUrl' => $user->getAvatarUrl(),
                    'role' => $member->getRole(),
                ];
            }

            return new JsonResponse(['members' => $data]);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Create a post in a group (members only)
     * POST /social/groups/{id}/post
     */
    #[Route('/{id}/post', name: 'create_post', methods: ['POST'])]
    public function createPost(int $id, Request $request): Response
    {
        try {
            $currentUserId = $this->getCurrentUserId();
            $currentUser = $this->em->getRepository(Users::class)->find($currentUserId);
            $group = $this->em->getRepository(Groups::class)->find($id);

            if (!$currentUser || !$group) {
                $this->addFlash('error', 'Groupe ou utilisateur non trouvé');
                return $this->redirectToRoute('social_groups_index');
            }

            // ✅ Permission check: Only members can post in groups
            $membership = $this->em->getRepository(Groupmember::class)->findOneBy([
                'groupId' => $group,
                'userId' => $currentUser,
            ]);

            if (!$membership) {
                $this->addFlash('error', 'Vous n\'êtes pas membre de ce groupe');
                return $this->redirectToRoute('social_groups_show', ['id' => $id]);
            }

            $content = $request->request->get('contenu');
            if (!$content || empty(trim($content))) {
                $this->addFlash('error', 'Le contenu ne peut pas être vide');
                return $this->redirectToRoute('social_groups_show', ['id' => $id]);
            }

            $publication = new Publication();
            $publication->setContenu($content);
            $publication->setAuthorId($currentUser);
            $publication->setDateCreation(new \DateTime());
            $publication->setStatut('ACTIF');
            $publication->setVisibility('GROUP');
            $publication->setGroupId($group);
            $publication->setImageUrl($request->request->get('imageUrl'));
            $publication->setNombreReactions(0);
            $publication->setNombreCommentaires(0);

            // Handle GIF URL
            $gifUrl = $request->request->get('gifUrl', null);
            if ($gifUrl) {
                $publication->setGifUrl($gifUrl);
            }

            $this->em->persist($publication);
            $this->em->flush();

            $this->addFlash('success', 'Publication créée avec succès');
            return $this->redirectToRoute('social_groups_show', ['id' => $id]);
        } catch (\Throwable $e) {
            $this->logger->error('Group post creation error: ' . $e->getMessage());
            $this->addFlash('error', 'Erreur lors de la création de la publication');
            return $this->redirectToRoute('social_groups_show', ['id' => $id]);
        }
    }

    #[Route('/{id}/search-users', name: 'search_users', methods: ['POST'])]
    public function searchUsers(int $id, Request $request): JsonResponse
    {
        try {
            $group = $this->em->getRepository(Groups::class)->find($id);
            if (!$group) {
                return new JsonResponse(['error' => 'Group not found'], 404);
            }

            $currentUserId = $this->getCurrentUserId();
            $query = trim($request->request->get('query', ''));
            $limit = (int)$request->request->get('limit', 10);

            $allUsers = $this->em->getRepository(Users::class)->findAll();
            $filteredUsers = [];
            
            foreach ($allUsers as $user) {
                if ($user->getId() === $currentUserId) {
                    continue;
                }

                $isMember = $this->em->getRepository(Groupmember::class)->findOneBy([
                    'groupId' => $group,
                    'userId' => $user,
                ]);
                if ($isMember) {
                    continue;
                }

                if ($query) {
                    $fullName = strtolower($user->getFirstName() . ' ' . $user->getLastName());
                    $username = strtolower($user->getUsername() ?? '');
                    $queryLower = strtolower($query);

                    if (!(strpos($fullName, $queryLower) !== false || strpos($username, $queryLower) !== false)) {
                        continue;
                    }
                }

                $filteredUsers[] = [
                    'id' => $user->getId(),
                    'firstName' => $user->getFirstName(),
                    'lastName' => $user->getLastName(),
                    'username' => $user->getUsername(),
                    'avatarUrl' => $user->getAvatarUrl(),
                ];
            }

            $filteredUsers = array_slice($filteredUsers, 0, $limit);
            return new JsonResponse(['users' => $filteredUsers]);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/{id}/add-member', name: 'add_member', methods: ['POST'])]
    public function addMember(int $id, Request $request): JsonResponse
    {
        try {
            $currentUserId = $this->getCurrentUserId();
            $currentUser = $this->em->getRepository(Users::class)->find($currentUserId);
            $group = $this->em->getRepository(Groups::class)->find($id);
            $targetUserId = (int)$request->request->get('userId');
            $targetUser = $this->em->getRepository(Users::class)->find($targetUserId);

            if (!$currentUser || !$group || !$targetUser) {
                return new JsonResponse(['error' => 'Invalid parameters'], 404);
            }

            $currentMembership = $this->em->getRepository(Groupmember::class)->findOneBy([
                'groupId' => $group,
                'userId' => $currentUser,
            ]);

            if (!$currentMembership || $currentMembership->getRole() !== 'ADMIN') {
                return new JsonResponse(['error' => 'Only admins can add members'], 403);
            }

            $existing = $this->em->getRepository(Groupmember::class)->findOneBy([
                'groupId' => $group,
                'userId' => $targetUser,
            ]);

            if ($existing) {
                return new JsonResponse(['error' => 'Already a member'], 400);
            }

            $member = new Groupmember();
            $member->setGroupId($group);
            $member->setUserId($targetUser);
            $member->setRole('MEMBER');
            $member->setJoinedAt(new \DateTime());

            $this->em->persist($member);
            $memberCount = $this->em->getRepository(Groupmember::class)->count(['groupId' => $group]);
            $group->setMemberCount($memberCount + 1);
            $this->em->flush();

            // Send notification to the added user
            $this->notificationController->notifyGroupMemberAdded($targetUserId, $currentUserId, $group->getName());

            return new JsonResponse([
                'success' => true,
                'message' => $targetUser->getFirstName() . ' ' . $targetUser->getLastName() . ' added to group'
            ]);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/{id}/post/{postId}/delete', name: 'delete_post', methods: ['POST'])]
    public function deletePost(int $id, int $postId, Request $request): Response
    {
        try {
            $currentUserId = $this->getCurrentUserId();
            $currentUser = $this->em->getRepository(Users::class)->find($currentUserId);
            $isAdmin = $currentUser && $currentUser->getRole() === 'ADMIN';
            $group = $this->em->getRepository(Groups::class)->find($id);
            $publication = $this->em->getRepository(Publication::class)->find($postId);

            if (!$currentUser || !$group || !$publication) {
                $this->addFlash('error', 'Publication non trouvée');
                return $this->redirectToRoute('social_groups_show', ['id' => $id]);
            }

            // Only the author or admin can delete
            if (!$isAdmin && $publication->getAuthorId()->getId() !== $currentUserId) {
                $this->addFlash('error', 'Vous ne pouvez supprimer que vos propres publications');
                return $this->redirectToRoute('social_groups_show', ['id' => $id]);
            }

            $this->em->remove($publication);
            $this->em->flush();

            $this->addFlash('success', 'Publication supprimée avec succès');
            return $this->redirectToRoute('social_groups_show', ['id' => $id]);
        } catch (\Throwable $e) {
            $this->logger->error('Group post deletion error: ' . $e->getMessage());
            $this->addFlash('error', 'Erreur lors de la suppression');
            return $this->redirectToRoute('social_groups_show', ['id' => $id]);
        }
    }

    #[Route('/{id}/post/{postId}/edit', name: 'edit_post', methods: ['POST'])]
    public function editPost(int $id, int $postId, Request $request): Response
    {
        try {
            $currentUserId = $this->getCurrentUserId();
            $currentUser = $this->em->getRepository(Users::class)->find($currentUserId);
            $isAdmin = $currentUser && $currentUser->getRole() === 'ADMIN';
            $group = $this->em->getRepository(Groups::class)->find($id);
            $publication = $this->em->getRepository(Publication::class)->find($postId);

            if (!$currentUser || !$group || !$publication) {
                $this->addFlash('error', 'Publication non trouvée');
                return $this->redirectToRoute('social_groups_show', ['id' => $id]);
            }

            // Only the author or admin can edit
            if (!$isAdmin && $publication->getAuthorId()->getId() !== $currentUserId) {
                $this->addFlash('error', 'Vous ne pouvez éditer que vos propres publications');
                return $this->redirectToRoute('social_groups_show', ['id' => $id]);
            }

            $contenu = trim($request->request->get('contenu', ''));
            
            // Validation du contenu
            if (!$contenu || strlen($contenu) < 3 || strlen($contenu) > 5000) {
                $this->addFlash('error', 'Le contenu doit contenir entre 3 et 5000 caractères');
                return $this->redirectToRoute('social_groups_show', ['id' => $id]);
            }

            $publication->setContenu(htmlspecialchars($contenu, ENT_QUOTES, 'UTF-8'));
            
            $this->em->flush();

            $this->addFlash('success', 'Publication modifiée avec succès');
            return $this->redirectToRoute('social_groups_show', ['id' => $id]);
        } catch (\Throwable $e) {
            $this->logger->error('Group post edit error: ' . $e->getMessage());
            $this->addFlash('error', 'Erreur lors de la modification');
            return $this->redirectToRoute('social_groups_show', ['id' => $id]);
        }
    }
    /**
     * Update group name (admin only)
     * POST /social/groups/{id}/update
     */
    #[Route('/{id}/update', name: 'update', methods: ['POST'])]
    public function update(int $id, Request $request): JsonResponse
    {
        try {
            $currentUserId = $this->getCurrentUserId();
            $currentUser = $this->em->getRepository(Users::class)->find($currentUserId);
            $group = $this->em->getRepository(Groups::class)->find($id);

            if (!$currentUser || !$group) {
                return new JsonResponse(['error' => 'Invalid parameters'], 404);
            }

            $membership = $this->em->getRepository(Groupmember::class)->findOneBy([
                'groupId' => $group,
                'userId' => $currentUser,
            ]);

            if (!$membership || $membership->getRole() !== 'ADMIN') {
                return new JsonResponse(['error' => 'Only admins can update group'], 403);
            }

            $name = $request->request->get('name');
            if (!$name || strlen($name) < 3 || strlen($name) > 100) {
                return new JsonResponse(['error' => 'Group name must be 3-100 characters'], 400);
            }

            $group->setName($name);
            $this->em->flush();

            return new JsonResponse(['success' => true, 'name' => $name]);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete group (admin only or platform admin)
     * POST /social/groups/{id}/delete
     */
    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(int $id, Request $request): Response
    {
        try {
            $currentUserId = $this->getCurrentUserId();
            $currentUser = $this->em->getRepository(Users::class)->find($currentUserId);
            $group = $this->em->getRepository(Groups::class)->find($id);
            $isPlatformAdmin = $currentUser && $currentUser->getRole() === 'ADMIN';

            if (!$currentUser || !$group) {
                throw $this->createNotFoundException('Group not found');
            }

            // Platform admin can delete any group
            if (!$isPlatformAdmin) {
                $membership = $this->em->getRepository(Groupmember::class)->findOneBy([
                    'groupId' => $group,
                    'userId' => $currentUser,
                ]);

                if (!$membership || $membership->getRole() !== 'ADMIN') {
                    throw $this->createAccessDeniedException('Only admins can delete group');
                }
            }

            // Delete all members
            $members = $this->em->getRepository(Groupmember::class)->findBy(['groupId' => $group]);
            foreach ($members as $member) {
                $this->em->remove($member);
            }

            // Delete all posts in the group
            $posts = $this->em->getRepository(Publication::class)->findBy(['groupId' => $group]);
            foreach ($posts as $post) {
                $this->em->remove($post);
            }

            // Delete the group
            $this->em->remove($group);
            $this->em->flush();

            $this->addFlash('success', 'Groupe supprimé avec succès');
            return $this->redirectToRoute('social_groups_index');
        } catch (\Throwable $e) {
            $this->logger->error('Group delete error: ' . $e->getMessage());
            $this->addFlash('error', 'Erreur lors de la suppression');
            return $this->redirectToRoute('social_groups_show', ['id' => $id]);
        }
    }

    /**
     * Remove member from group (admin only)
     * POST /social/groups/{id}/remove-member/{memberId}
     */
    #[Route('/{id}/remove-member/{memberId}', name: 'remove_member', methods: ['POST'])]
    public function removeMember(int $id, int $memberId, Request $request): Response
    {
        try {
            $currentUserId = $this->getCurrentUserId();
            $currentUser = $this->em->getRepository(Users::class)->find($currentUserId);
            $group = $this->em->getRepository(Groups::class)->find($id);
            $memberToRemove = $this->em->getRepository(Users::class)->find($memberId);

            if (!$currentUser || !$group || !$memberToRemove) {
                throw $this->createNotFoundException('Invalid parameters');
            }

            $adminMembership = $this->em->getRepository(Groupmember::class)->findOneBy([
                'groupId' => $group,
                'userId' => $currentUser,
            ]);

            if (!$adminMembership || $adminMembership->getRole() !== 'ADMIN') {
                throw $this->createAccessDeniedException('Only admins can remove members');
            }

            $membershipToRemove = $this->em->getRepository(Groupmember::class)->findOneBy([
                'groupId' => $group,
                'userId' => $memberToRemove,
            ]);

            if ($membershipToRemove) {
                $this->em->remove($membershipToRemove);
                $this->em->flush();
                $this->addFlash('success', $memberToRemove->getFirstName() . ' a été retiré du groupe');
            }

            return $this->redirectToRoute('social_groups_show', ['id' => $id]);
        } catch (\Throwable $e) {
            $this->logger->error('Remove member error: ' . $e->getMessage());
            $this->addFlash('error', 'Erreur lors du retrait du membre');
            return $this->redirectToRoute('social_groups_show', ['id' => $id]);
        }
    }}
