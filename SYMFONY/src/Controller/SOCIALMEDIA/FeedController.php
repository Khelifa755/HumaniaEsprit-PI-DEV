<?php
namespace App\Controller\SOCIALMEDIA;
use App\Entity\Publication;
use App\Entity\Commentaire;
use App\Entity\Reaction;
use App\Entity\Savedpost;
use App\Entity\Share;
use App\Entity\Users;
use App\Entity\Notification;
use App\Entity\Groups;
use App\Entity\Groupmember;
use App\Entity\Follow;
use App\Entity\Mention;
use App\Repository\SOCIALMEDIA\PublicationRepository;
use App\Repository\SOCIALMEDIA\ReactionRepository;
use App\Repository\SOCIALMEDIA\SavedpostRepository;
use App\Repository\SOCIALMEDIA\CommentaireRepository;
use App\Repository\SOCIALMEDIA\ShareRepository;
use App\Repository\SOCIALMEDIA\NotificationRepository;
use App\Repository\SOCIALMEDIA\MentionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;

#[Route('/social', name: 'social_')]
class FeedController extends AbstractController {
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
        private PublicationRepository $pubRepo, 
        private ReactionRepository $reactionRepo, 
        private SavedpostRepository $savedRepo, 
        private CommentaireRepository $commentRepo, 
        private ShareRepository $shareRepo, 
        private NotificationRepository $notifRepo,
        private HttpClientInterface $httpClient
    ) {}
    #[Route('/feed', name: 'feed', methods: ['GET'])]
    public function feed(Request $request): Response {
        $currentUserId = $this->getCurrentUserId();
        $user = $this->getUser();
        // ✅ APRÈS
    $currentUser = $user instanceof Users ? $user : $this->em->getReference(Users::class, $currentUserId);
        $mode = $request->query->get('mode', 'home');
        $isAdmin = $currentUser && $currentUser->getRole() === 'ADMIN';
        
        if ($mode === 'popular') {
            $publications = $this->pubRepo->findPopular(20);
        } elseif ($mode === 'saved') {
            $publications = $this->pubRepo->findSavedByUser($currentUserId, 20);
        } else {
            // Reduced from 4 JOINs to 2 (removed nested sharedFromId.authorId JOIN)
            $allPublications = $this->em->createQueryBuilder()
    ->select('p', 'u', 'g')
    ->from(Publication::class, 'p')
    ->leftJoin('p.authorId', 'u')->addSelect('u')
    ->leftJoin('p.groupId', 'g')->addSelect('g')
    ->where('p.statut != :suppr')
    ->setParameter('suppr', 'SUPPRIME')
    ->orderBy('p.dateCreation', 'DESC')
    ->setMaxResults(50)
    ->getQuery()
    ->getResult();

            if ($isAdmin) {
                $publications = $allPublications;
            } else {
                $followedUsers = $this->em->getRepository(Follow::class)->findBy([
                    'followerId' => $currentUser,
                    'status' => 'ACTIVE',
                ]);
                $followedUserIds = [];
                foreach ($followedUsers as $follow) {
                    $followingUser = $follow->getFollowingId();
                    if ($followingUser) {
                        $followedUserIds[] = $followingUser->getId();
                    }
                }

                $userGroupMemberships = $this->em->getRepository(Groupmember::class)->findBy([
                    'userId' => $currentUser,
                ]);
                $userGroupIds = [];
                foreach ($userGroupMemberships as $membership) {
                    $group = $membership->getGroupId();
                    if ($group) {
                        $userGroupIds[] = $group->getId();
                    }
                }

                $publications = [];
                foreach ($allPublications as $pub) {
                    if ($pub->getStatut() === 'SUPPRIME') {
                        continue;
                    }
                    $author = $pub->getAuthorId();
                    if (!$author) {
                        continue;
                    }
                    $authorId = $author->getId();
                    $visibility = $pub->getVisibility();
                    $groupId = $pub->getGroupId() ? $pub->getGroupId()->getId() : null;

                    if (($visibility === 'PUBLIC' && (in_array($authorId, $followedUserIds, true) || $authorId === $currentUserId))
                        || ($visibility === 'GROUP' && in_array($groupId, $userGroupIds, true))) {
                        $publications[] = $pub;
                    }
                }
            }

            usort($publications, static function ($a, $b) {
                return $b->getDateCreation()->getTimestamp() <=> $a->getDateCreation()->getTimestamp();
            });
            $publications = array_slice($publications, 0, 20);
        }
        
        $pubIds = array_map(static fn ($p) => $p->getId(), $publications);

        $userReactions = array_fill_keys($pubIds, null);
        $userSaved = array_fill_keys($pubIds, false);
        $userShares = array_fill_keys($pubIds, false);
        $shareCount = array_fill_keys($pubIds, 0);
        $reactionStats = array_fill_keys($pubIds, []);

        if ($pubIds !== []) {
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

            $shareCount = $this->shareRepo->countGroupedByPublicationIds($pubIds);
            foreach ($pubIds as $pid) {
                if (!isset($shareCount[$pid])) {
                    $shareCount[$pid] = 0;
                }
            }

            $reactionStats = $this->reactionRepo->aggregateCountsByPublicationIds($pubIds);
            foreach ($pubIds as $pid) {
                if (!isset($reactionStats[$pid])) {
                    $reactionStats[$pid] = [];
                }
            }
        }

        $commentsByPublication = $pubIds !== []
            ? $this->commentRepo->findActiveByPublicationIdsGrouped($pubIds)
            : [];

        // ── SUGGESTIONS D'UTILISATEURS ──
        $suggestedUsers = $this->getSuggestedUsers($currentUserId);
        
        $memberGroupIdRows = $this->em->createQueryBuilder()
            ->select('IDENTITY(m.groupId) AS gid')
            ->from(Groupmember::class, 'm')
            ->where('IDENTITY(m.userId) = :uid')
            ->setParameter('uid', $currentUserId)
            ->getQuery()
            ->getArrayResult();
        $memberGroupIds = array_values(array_unique(array_map(static fn (array $r) => (int) $r['gid'], $memberGroupIdRows)));

        $userGroupsData = [];
        if ($memberGroupIds !== []) {
            $userGroupsData = $this->em->createQueryBuilder()
                ->select('g')
                ->from(Groups::class, 'g')
                ->where('g.id IN (:ids)')
                ->setParameter('ids', $memberGroupIds)
                ->setMaxResults(50)
                ->getQuery()
                ->getResult();
        }

        // ── SIDEBAR DATA ──
        $userSavedCount = $this->savedRepo->countByUser($currentUserId);
        
        // Followers count (people who follow this user) - using QueryBuilder like profile page
        $qb = $this->em->createQueryBuilder();
        $followersCount = $qb
            ->select('COUNT(f.id)')
            ->from(\App\Entity\Follow::class, 'f')
            ->where('f.followingId = :userId')
            ->andWhere("f.status = 'ACTIVE'")
            ->setParameter('userId', $currentUserId)
            ->getQuery()
            ->getSingleScalarResult();
        
        // Following count (people this user follows) - using QueryBuilder like profile page
        $qb = $this->em->createQueryBuilder();
        $followingCount = $qb
            ->select('COUNT(f.id)')
            ->from(\App\Entity\Follow::class, 'f')
            ->where('f.followerId = :userId')
            ->andWhere("f.status = 'ACTIVE'")
            ->setParameter('userId', $currentUserId)
            ->getQuery()
            ->getSingleScalarResult();
        
        // User profile (bio, job title)
        $userProfile = $this->em->getRepository(\App\Entity\Userprofile::class)->findOneBy([
            'userId' => $currentUser
        ]);
        
        // Events count
        $eventsCount = 0; // TODO: Query Evenement table if needed
        
        // Birthdays count
        $birthdaysCount = 0; // TODO: Query Users table for upcoming birthdays

        // ── TRENDING TOPICS (Hybrid: Keywords + Departments) ──
        $trendingTopics = $this->getTrendingTopics(5);

        return $this->render('SOCIALMEDIA/feed/index.html.twig', [
            'publications' => $publications, 
            'userReactions' => $userReactions, 
            'userSaved' => $userSaved, 
            'shareCount' => $shareCount, 
            'userShares' => $userShares, 
            'reactionStats' => $reactionStats, 
            'currentUser' => $currentUser, 
            'currentUserId' => $currentUserId, 
            'commentsByPublication' => $commentsByPublication,
            'suggestedUsers' => $suggestedUsers,
            'userGroups' => $userGroupsData,
            'trendingTopics' => $trendingTopics,
            'notifications' => $this->notifRepo->findByUserId($currentUserId, 50),
            'userSavedCount' => $userSavedCount,
            'followersCount' => $followersCount,
            'followingCount' => $followingCount,
            'eventsCount' => $eventsCount,
            'birthdaysCount' => $birthdaysCount,
            'userProfile' => $userProfile,
        ]);
    }
    #[Route('/feed/post', name: 'feed_post_create', methods: ['POST'])]
    public function createPost(Request $request): Response {
        $contenu = trim($request->request->get('contenu', ''));
        $visibility = $request->request->get('visibility', 'PUBLIC');
        $groupId = $request->request->get('groupId', null);
        $gifUrl = $request->request->get('gifUrl', null);
        
        // Validation du contenu
        $error = $this->validateContent($contenu, 3, 5000, 'publication');
        if ($error) {
            $this->addFlash('error', $error);
            return $this->redirectToRoute('social_feed');
        }
        
        // If visibility is GROUP, groupId must be provided
        if ($visibility === 'GROUP' && !$groupId) {
            $this->addFlash('error', 'Veuillez sélectionner un groupe');
            return $this->redirectToRoute('social_feed');
        }
        
        $author = $this->em->getReference(Users::class, $this->getCurrentUserId());
        $pub = new Publication();
        $pub->setContenu($contenu);
        $pub->setAuthorId($author);
        $pub->setDateCreation(new \DateTime());
        $pub->setStatut('ACTIF');
        $pub->setVisibility($visibility);
        $pub->setNombreCommentaires(0);
        $pub->setNombreReactions(0);
        
        // Set group if provided (use getReference - only setting relationship)
        if ($groupId) {
            try {
                $group = $this->em->getReference(Groups::class, (int) $groupId);
                $pub->setGroupId($group);
            } catch (\Exception $e) {
                // Group not found, ignore
            }
        }

        // Handle image upload
        $file = $request->files->get('image');
        if ($file) {
            $filename = uniqid() . '.' . $file->guessExtension();
            $file->move($this->getParameter('kernel.project_dir') . '/public/uploads/social', $filename);
            $pub->setImageUrl('/uploads/social/' . $filename);
        }

        // Handle GIF URL
        if ($gifUrl) {
            $pub->setGifUrl($gifUrl);
        }

        $this->em->persist($pub);
        $this->em->flush();
        return $this->redirectToRoute('social_feed');
    }
    #[Route('/feed/react/{id}', name: 'feed_react', methods: ['POST'])]
    public function react(Request $request, int $id): Response {
        $type = $request->request->get('type', 'LIKE');
        $userId = $this->getCurrentUserId();
        $user = $this->em->getReference(Users::class, $userId);
        $pub = $this->pubRepo->find($id);
        if (!$pub) { return $this->redirectToRoute('social_feed'); }
        $existing = $this->reactionRepo->findByUserAndPublication($userId, $id);
        if ($existing) {
            if ($existing->getType() === $type) {
                $this->em->remove($existing);
                $pub->setNombreReactions(max(0, $pub->getNombreReactions() - 1));
            } else {
                $existing->setType($type);
            }
        } else {
            $reaction = new Reaction();
            $reaction->setType($type);
            $reaction->setUserId($user);
            $reaction->setPublicationId($pub);
            $reaction->setDateCreation(new \DateTime());
            $this->em->persist($reaction);
            $pub->setNombreReactions($pub->getNombreReactions() + 1);
            
            // Notify publication author (skip for system posts with no author)
            $authorId = $pub->getAuthorId()?->getId();
            if ($authorId !== null) {
                $this->notifyReaction($authorId, $userId, $id);
            }
        }
        $this->em->flush();
        return $this->getSmartRedirectResponse($request);
    }
    #[Route('/feed/comment/{id}', name: 'feed_comment', methods: ['POST'])]
    public function comment(Request $request, int $id): Response {
        $contenu = trim($request->request->get('contenu', ''));
        $gifUrl = trim($request->request->get('gifUrl', ''));
        
        // Validation du contenu
        $error = $this->validateContent($contenu, 1, 500, 'commentaire');
        if ($error) {
            $this->addFlash('error', $error);
            return $this->redirectToRoute('social_feed');
        }
        
        $pub = $this->pubRepo->find($id);
        $author = $this->em->getReference(Users::class, $this->getCurrentUserId());
        if (!$pub) { 
            return $this->redirectToRoute('social_feed');
        }
        
        $comment = new Commentaire();
        $comment->setContenu($contenu);
        $comment->setPublicationId($pub);
        $comment->setAuthorId($author);
        $comment->setDateCreation(new \DateTime());
        $comment->setStatut('ACTIF');
        $comment->setNombreReactions(0);
        if ($gifUrl) {
            $comment->setGifUrl($gifUrl);
        }
        $pub->setNombreCommentaires($pub->getNombreCommentaires() + 1);
        $this->em->persist($comment);
        
        // Notify publication author (skip for system posts with no author)
        $pubAuthorId = $pub->getAuthorId()?->getId();
        if ($pubAuthorId !== null) {
            $this->notifyComment($pubAuthorId, $this->getCurrentUserId(), $id, $contenu);
        }
        
        $this->em->flush();
        return $this->getSmartRedirectResponse($request);
    }
    #[Route('/feed/save/{id}', name: 'feed_save', methods: ['POST'])]
    public function save(Request $request, int $id): Response {
        $userId = $this->getCurrentUserId();
        $user = $this->em->getReference(Users::class, $userId);
        $existing = $this->savedRepo->findByUserAndPublication($userId, $id);
        if ($existing) { $this->em->remove($existing); } else {
            $pub = $this->pubRepo->find($id);
            if (!$pub) { return $this->redirectToRoute('social_feed'); }
            $saved = new Savedpost();
            $saved->setUserId($user);
            $saved->setPublicationId($pub);
            $saved->setSavedAt(new \DateTime());
            $this->em->persist($saved);
        }
        $this->em->flush();
        return $this->getSmartRedirectResponse($request);
    }
    #[Route('/feed/delete/{id}', name: 'feed_delete', methods: ['POST'])]
    public function delete(Request $request, int $id): Response {
        $pub = $this->pubRepo->find($id);
        $currentUserId = $this->getCurrentUserId();
        $currentUser = $this->em->getRepository(Users::class)->find($currentUserId);
        $isAdmin = $currentUser && $currentUser->getRole() === 'ADMIN';
        
        // Only author or admin can delete
        if (!$pub || (!$isAdmin && $pub->getAuthorId()?->getId() !== $currentUserId)) { 
            return $this->redirectToRoute('social_feed'); 
        }
        $pub->setStatut('SUPPRIME');
        $this->em->flush();
        return $this->getSmartRedirectResponse($request);
    }
    #[Route('/feed/delete-comment/{id}', name: 'feed_delete_comment', methods: ['POST'])]
    public function deleteComment(Request $request, int $id): Response {
        $comment = $this->commentRepo->find($id);
        $currentUserId = $this->getCurrentUserId();
        $currentUser = $this->em->getRepository(Users::class)->find($currentUserId);
        $isAdmin = $currentUser && $currentUser->getRole() === 'ADMIN';
        
        // Only author or admin can delete
        if (!$comment || (!$isAdmin && $comment->getAuthorId()->getId() !== $currentUserId)) { 
            return $this->redirectToRoute('social_feed'); 
        }
        $pub = $comment->getPublicationId();
        $pub->setNombreCommentaires(max(0, $pub->getNombreCommentaires() - 1));
        $comment->setStatut('SUPPRIME');
        $this->em->flush();
        return $this->getSmartRedirectResponse($request);
    }
    #[Route('/feed/edit-comment/{id}', name: 'feed_edit_comment', methods: ['POST'])]
    public function editComment(Request $request, int $id): Response {
        $comment = $this->commentRepo->find($id);
        $currentUserId = $this->getCurrentUserId();
        $currentUser = $this->em->getRepository(Users::class)->find($currentUserId);
        $isAdmin = $currentUser && $currentUser->getRole() === 'ADMIN';
        
        // Only author or admin can edit
        if (!$comment || (!$isAdmin && $comment->getAuthorId()->getId() !== $currentUserId)) { 
            return $this->redirectToRoute('social_feed'); 
        }
        $contenu = trim($request->request->get('contenu', ''));
        
        // Validation du contenu
        $error = $this->validateContent($contenu, 1, 500, 'commentaire');
        if ($error) {
            $this->addFlash('error', $error);
            return $this->redirectToRoute('social_feed');
        }
        
        $comment->setContenu($contenu);
        $this->em->flush();
        return $this->getSmartRedirectResponse($request);
    }
    #[Route('/feed/edit/{id}', name: 'feed_edit', methods: ['POST'])]
    public function editPost(Request $request, int $id): Response {
        $pub = $this->pubRepo->find($id);
        $currentUserId = $this->getCurrentUserId();
        $currentUser = $this->em->getRepository(Users::class)->find($currentUserId);
        $isAdmin = $currentUser && $currentUser->getRole() === 'ADMIN';
        
        // Only author or admin can edit
        if (!$pub || (!$isAdmin && $pub->getAuthorId()?->getId() !== $currentUserId)) {
            return $this->redirectToRoute('social_feed');
        }
        $contenu = trim($request->request->get('contenu', ''));
        $visibility = $request->request->get('visibility', 'PUBLIC');
        $groupId = $request->request->get('groupId', null);
        
        // Validation du contenu
        $error = $this->validateContent($contenu, 3, 5000, 'publication');
        if ($error) {
            $this->addFlash('error', $error);
            return $this->redirectToRoute('social_feed');
        }
        
        // Validate group if visibility is GROUP
        if ($visibility === 'GROUP' && !$groupId) {
            $this->addFlash('error', 'Veuillez sélectionner un groupe.');
            return $this->redirectToRoute('social_feed');
        }
        
        $pub->setContenu($contenu);
        $pub->setVisibility($visibility);
        
        // Handle group
        if ($visibility === 'GROUP' && $groupId) {
            $group = $this->em->getRepository(Groups::class)->find($groupId);
            if ($group) {
                $pub->setGroupId($group);
            }
        } else {
            $pub->setGroupId(null);
        }
        
        $file = $request->files->get('image');
        if ($file) {
            $filename = uniqid() . '.' . $file->guessExtension();
            $file->move($this->getParameter('kernel.project_dir') . '/public/uploads/social', $filename);
            $pub->setImageUrl('/uploads/social/' . $filename);
        }
        $this->em->flush();
        return $this->getSmartRedirectResponse($request);
    }
    #[Route('/feed/react-comment/{id}', name: 'feed_react_comment', methods: ['POST'])]
    public function reactComment(Request $request, int $id): Response {
        $type = $request->request->get('type', 'LIKE');
        $userId = $this->getCurrentUserId();
        $user = $this->em->getReference(Users::class, $userId);
        $comment = $this->commentRepo->find($id);
        if (!$comment) { return $this->redirectToRoute('social_feed'); }
        $pubId = $comment->getPublicationId()->getId();
        $existing = $this->reactionRepo->findOneBy(['userId' => $user, 'commentaireId' => $comment]);
        if ($existing) {
            if ($existing->getType() === $type) {
                $this->em->remove($existing);
                $comment->setNombreReactions(max(0, $comment->getNombreReactions() - 1));
            } else {
                $existing->setType($type);
            }
        } else {
            $reaction = new Reaction();
            $reaction->setType($type);
            $reaction->setUserId($user);
            $reaction->setCommentaireId($comment);
            $reaction->setDateCreation(new \DateTime());
            $this->em->persist($reaction);
            $comment->setNombreReactions($comment->getNombreReactions() + 1);
        }
        $this->em->flush();
        return $this->getSmartRedirectResponse($request);
    }

    #[Route('/feed/share/{id}', name: 'feed_share', methods: ['POST'])]
    public function share(Request $request, int $id): Response
    {
        $userId = $this->getCurrentUserId();
        $user = $this->em->getReference(Users::class, $userId);
        $originalPub = $this->pubRepo->find($id);

        if (!$originalPub) {
            return $this->redirectToRoute('social_feed');
        }

        // Vérifier si l'utilisateur a déjà partagé cette publication
        $existingShare = $this->shareRepo->findByUserAndPublication($userId, $id);
        
        if ($existingShare) {
            // Si déjà partagé, supprimer la publication de partage et l'entry share
            $sharedPub = $this->pubRepo->findOneBy(['sharedFromId' => $originalPub, 'authorId' => $user]);
            if ($sharedPub) {
                $this->em->remove($sharedPub);
            }
            $this->em->remove($existingShare);
        } else {
            // Créer une nouvelle Publication de partage
            $shareMessage = trim($request->request->get('shareMessage', ''));
            
            $sharedPost = new Publication();
            $sharedPost->setContenu($shareMessage ?: ''); // Contenu = message optionnel du partage
            $sharedPost->setAuthorId($user);
            $sharedPost->setSharedFromId($originalPub);
            $sharedPost->setShareMessage($shareMessage ?: null);
            $sharedPost->setDateCreation(new \DateTime());
            $sharedPost->setStatut('ACTIF');
            $sharedPost->setVisibility($request->request->get('visibility', 'PUBLIC'));
            $sharedPost->setNombreCommentaires(0);
            $sharedPost->setNombreReactions(0);
            
            $this->em->persist($sharedPost);

            // Créer aussi une entry Share pour tracker
            $share = new Share();
            $share->setUserId($user);
            $share->setPublicationId($originalPub);
            $share->setSharedAt(new \DateTime());
            $share->setSharedMessage($shareMessage ?: null);
            $share->setShareCount(0);
            
            $this->em->persist($share);
            
            // Notify original publication author (skip for system posts with no author)
            $origAuthorId = $originalPub->getAuthorId()?->getId();
            if ($origAuthorId !== null) {
                $this->notifyShare($origAuthorId, $userId, $id);
            }
        }

        $this->em->flush();
        return $this->getSmartRedirectResponse($request);
    }

    // ════════════════════════════════════════════════════════════
    // SMART REDIRECT HELPER
    // ════════════════════════════════════════════════════════════
    
    /**
     * Redirect back to the page where the action was performed (from returnUrl or Referer)
     * Falls back to social_feed if no referrer is available
     */
    private function getSmartRedirectResponse(Request $request): Response
    {
        // 1. Check for explicit returnUrl parameter (passed from form)
        $returnUrl = $request->request->get('returnUrl');
        if ($returnUrl && $this->isValidRedirectUrl($returnUrl, $request)) {
            return $this->redirect($returnUrl);
        }

        // 2. Fall back to Referer header
        $referer = $request->headers->get('referer');
        if ($referer && $this->isValidRedirectUrl($referer, $request)) {
            return $this->redirect($referer);
        }

        // 3. Default fallback to feed home
        return $this->redirectToRoute('social_feed');
    }

    /**
     * Validate that redirect URL is safe (same domain)
     */
    private function isValidRedirectUrl(string $url, Request $request): bool
    {
        if (empty($url)) {
            return false;
        }
        
        // Only allow relative URLs or URLs from the same host
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return strpos($url, 'http://' . $host) === 0 || 
               strpos($url, 'https://' . $host) === 0 || 
               strpos($url, '/') === 0;
    }

    // ════════════════════════════════════════════════════════════
    // PRIVATE NOTIFICATION HELPERS
    // ════════════════════════════════════════════════════════════

    /**
     * Créer une notification de LIKE/REACTION
     */
    private function notifyReaction(int $targetUserId, int $reactorUserId, int $publicationId): void
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
    private function notifyComment(int $targetUserId, int $commenterUserId, int $publicationId, string $previewText = ''): void
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
    private function notifyShare(int $targetUserId, int $sharerUserId, int $publicationId): void
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
     * Valider le contenu d'une publication ou commentaire
     */
    private function validateContent(string $content, int $minLength, int $maxLength, string $type = 'publication'): ?string
    {
        // Longueur après trim
        if (strlen($content) < $minLength) {
            if ($type === 'commentaire') {
                return 'Le commentaire ne peut pas être vide.';
            }
            return "La publication doit contenir au moins $minLength caractères.";
        }

        if (strlen($content) > $maxLength) {
            $typeName = $type === 'commentaire' ? 'Le commentaire' : 'La publication';
            return "$typeName dépasse la limite ($maxLength caractères maximum).";
        }

        // Vérifier les URLs excessives (anti-spam)
        $urlCount = substr_count($content, 'http') + substr_count($content, 'www');
        if ($urlCount > 2) {
            return "Trop de liens (maximum 2 par $type).";
        }

        // Vérifier les caractères répétés (anti-spam)
        if (preg_match('/(.)\1{5,}/', $content)) {
            return "Les caractères répétés en excès ne sont pas autorisés.";
        }

        return null; // Valide
    }

    /**
     * Improve text with AI
     * POST /social/feed/ai/improve
     */
    #[Route('/feed/ai/improve', name: 'ai_improve', methods: ['POST'])]
    public function improveText(Request $request): Response
    {
        try {
            $groqService = new GroqAIService($this->httpClient);
            $data = json_decode($request->getContent(), true) ?? [];
            $text = $data['text'] ?? '';
            
            if (empty($text)) {
                return $this->json(['error' => 'Texte vide'], 400);
            }

            $improved = $groqService->improveText($text);
            return $this->json(['success' => true, 'text' => $improved]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Summarize text with AI
     * POST /social/feed/ai/summarize
     */
    #[Route('/feed/ai/summarize', name: 'ai_summarize', methods: ['POST'])]
    public function summarizeText(Request $request): Response
    {
        try {
            $groqService = new GroqAIService($this->httpClient);
            $data = json_decode($request->getContent(), true) ?? [];
            $text = $data['text'] ?? '';
            
            if (empty($text)) {
                return $this->json(['error' => 'Texte vide'], 400);
            }

            $summary = $groqService->summarize($text);
            return $this->json(['success' => true, 'text' => $summary]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Generate post with AI
     * POST /social/feed/ai/generate
     */
    #[Route('/feed/ai/generate', name: 'ai_generate', methods: ['POST'])]
    public function generatePost(Request $request): Response
    {
        try {
            $groqService = new GroqAIService($this->httpClient);
            $data = json_decode($request->getContent(), true) ?? [];
            $subject = $data['subject'] ?? '';
            $tone = $data['tone'] ?? 'professionnel';
            
            if (empty($subject)) {
                return $this->json(['error' => 'Sujet vide'], 400);
            }

            $post = $groqService->generatePost($subject, $tone);
            return $this->json(['success' => true, 'text' => $post]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Search GIFs
     * POST /social/feed/gifs/search
     */
    #[Route('/feed/gifs/search', name: 'gifs_search', methods: ['POST'])]
    public function searchGifs(Request $request): Response
    {
        try {
            $giphyService = new GiphyService($this->httpClient);
            $data = json_decode($request->getContent(), true) ?? [];
            $query = $data['query'] ?? '';
            $limit = (int)($data['limit'] ?? 10);
            
            if (empty($query)) {
                return $this->json(['error' => 'Requête vide'], 400);
            }

            $gifs = $giphyService->search($query, $limit);
            return $this->json(['success' => true, 'gifs' => $gifs]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get trending GIFs
     * POST /social/feed/gifs/trending
     */
    #[Route('/feed/gifs/trending', name: 'gifs_trending', methods: ['POST'])]
    public function trendingGifs(Request $request): Response
    {
        try {
            $giphyService = new GiphyService($this->httpClient);
            $data = json_decode($request->getContent(), true) ?? [];
            $limit = (int)($data['limit'] ?? 10);
            
            $gifs = $giphyService->trending($limit);
            return $this->json(['success' => true, 'gifs' => $gifs]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Recherche combinée: posts + utilisateurs
     * POST /social/feed/search
     */
    #[Route('/feed/search', name: 'feed_search', methods: ['POST'])]
    public function search(Request $request): Response
    {
        $query = trim($request->request->get('q', ''));
        
        if (strlen($query) < 2) {
            return $this->json(['posts' => [], 'users' => []]);
        }

        $query_lower = strtolower($query);

        $allPubs = $this->em->createQueryBuilder()
            ->select('p', 'a')
            ->from(Publication::class, 'p')
            ->leftJoin('p.authorId', 'a')
            ->where('p.statut != :suppr')
            ->setParameter('suppr', 'SUPPRIME')
            ->orderBy('p.dateCreation', 'DESC')
            ->setMaxResults(200)
            ->getQuery()
            ->getResult();

        $matchingPosts = [];
        foreach ($allPubs as $pub) {
            if ($pub->getStatut() === 'SUPPRIME') {
                continue;
            }
            $author = $pub->getAuthorId();
            $authorPart = $author
                ? strtolower((string) $author->getFirstName() . ' ' . (string) $author->getLastName())
                : '';
            $searchableText = strtolower($pub->getContenu() . ' ' . $authorPart);

            if (str_contains($searchableText, $query_lower)) {
                $matchingPosts[] = $pub->getId();
            }
        }

        $allUsers = $this->em->createQueryBuilder()
            ->select('u')
            ->from(Users::class, 'u')
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(150)
            ->getQuery()
            ->getResult();

        $matchingUsers = [];
        foreach ($allUsers as $user) {
            $searchableText = strtolower(
                $user->getFirstName() . ' ' . 
                $user->getLastName() . ' ' .
                $user->getUsername()
            );
            
            if (strpos($searchableText, $query_lower) !== false) {
                $matchingUsers[] = [
                    'id' => $user->getId(),
                    'firstName' => $user->getFirstName(),
                    'lastName' => $user->getLastName(),
                    'username' => $user->getUsername(),
                    'avatarUrl' => $user->getAvatarUrl(),
                ];
            }
        }

        return $this->json([
            'posts' => $matchingPosts,
            'users' => array_slice($matchingUsers, 0, 5),
        ]);
    }

    /**
     * Get all reactions for a publication with user details
     * GET /social/feed/reactions/{id}
     */
    #[Route('/feed/reactions/{id}', name: 'feed_reactions', methods: ['GET'])]
    public function getReactions(int $id): Response
    {
        $pub = $this->pubRepo->find($id);
        if (!$pub) {
            return $this->json(['error' => 'Post not found'], 404);
        }

        $reactions = $this->reactionRepo->createQueryBuilder('r')
            ->leftJoin('r.userId', 'u')
            ->addSelect('u')
            ->where('r.publicationId = :pub')
            ->setParameter('pub', $pub)
            ->getQuery()
            ->getResult();
        
        // Group reactions by type and collect user data
        $grouped = [];
        $reactionTypes = ['LIKE' => '👍', 'LOVE' => '❤️', 'HAHA' => '😂', 'WOW' => '😮', 'SAD' => '😢', 'ANGRY' => '😠'];
        
        foreach ($reactionTypes as $type => $emoji) {
            $grouped[$type] = [
                'emoji' => $emoji,
                'count' => 0,
                'users' => []
            ];
        }

        foreach ($reactions as $reaction) {
            $type = $reaction->getType();
            if (!isset($grouped[$type])) {
                $grouped[$type] = [
                    'emoji' => '👍',
                    'count' => 0,
                    'users' => []
                ];
            }
            
            $user = $reaction->getUserId();
            if ($user) {
                $grouped[$type]['users'][] = [
                    'id' => $user->getId(),
                    'firstName' => $user->getFirstName(),
                    'lastName' => $user->getLastName(),
                    'username' => $user->getUsername(),
                    'avatarUrl' => $user->getAvatarUrl(),
                ];
                $grouped[$type]['count']++;
            }
        }

        // Remove empty types
        $grouped = array_filter($grouped, fn($item) => $item['count'] > 0);

        return $this->json(['reactions' => $grouped]);
    }

    /**
     * Obtenir les utilisateurs suggérés à suivre
     * Retourne les utilisateurs que l'utilisateur courant ne suit pas encore
     */
    private function getSuggestedUsers(int $currentUserId): array
    {
        $followingRows = $this->em->createQueryBuilder()
            ->select('IDENTITY(f.followingId) AS fid')
            ->from(Follow::class, 'f')
            ->where('IDENTITY(f.followerId) = :me')
            ->andWhere('f.status IN (:statuses)')
            ->setParameter('me', $currentUserId)
            ->setParameter('statuses', ['ACTIVE', 'PENDING'])
            ->getQuery()
            ->getArrayResult();

        $followingIds = array_map(static fn (array $r) => (int) $r['fid'], $followingRows);

        $qb = $this->em->createQueryBuilder()
            ->select('u')
            ->from(Users::class, 'u')
            ->where('u.id != :me')
            ->setParameter('me', $currentUserId)
            ->setMaxResults(6);

        if ($followingIds !== []) {
            $qb->andWhere('u.id NOT IN (:following)')
                ->setParameter('following', $followingIds);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Obtenir les trending topics (approche hybride)
     * Combine: Extraction de mots-clés + Départements mentionnés
     */
    private function getTrendingTopics(int $limit = 20): array
    {
        // French stop words à ignorer
        $stopWords = [
            'le', 'la', 'les', 'de', 'du', 'des', 'et', 'ou', 'mais', 'donc',
            'un', 'une', 'des', 'a', 'au', 'aux', 'par', 'pour', 'avec', 'sans',
            'sur', 'sous', 'dans', 'entre', 'vers', 'chez', 'en', 'je', 'tu',
            'il', 'elle', 'nous', 'vous', 'ils', 'elles', 'ce', 'cette', 'cet',
            'ces', 'mon', 'ton', 'son', 'ma', 'ta', 'sa', 'mes', 'tes', 'ses',
            'notre', 'votre', 'leur', 'nos', 'vos', 'leurs', 'qui', 'que', 'quoi',
            'est', 'suis', 'es', 'sommes', 'êtes', 'sont', 'être', 'ont', 'a',
            'été', 'étés', 'étaient', 'serais', 'serait', 'serons', 'seriez',
            'seront', 'seraient', 'étais', 'était', 'étions', 'étiez', 'avait',
            'avaient', 'aura', 'aurait', 'aurais', 'auraient', 'aie', 'aies',
            'aient', 'aussi', 'bien', 'bon', 'c', 'ça', 'cas', 'cher', 'chez',
            'chose', 'coi', 'comme', 'comment', 'comparable', 'comparables',
            'compris', 'contre', 'courant', 'court', 'd', 'da', 'dans', 'de',
            'debout', 'debut', 'delà', 'dememe', 'demesme', 'demieux',
            'demoiselle', 'denomme', 'denonmmes', 'denonces', 'denommees',
            'denommes', 'denoncer', 'denonciations', 'denoncees', 'denoncees',
            'denonce', 'denoncer', 'denoncerait', 'denonceraient',
            'the', 'a', 'an', 'and', 'are', 'as', 'at', 'be', 'by', 'for',
            'from', 'has', 'he', 'in', 'is', 'it', 'its', 'of', 'on', 'or',
            'that', 'the', 'to', 'was', 'will', 'with', 'you', 'your',
            'i', 'me', 'my', 'we', 'what', 'when', 'where', 'which', 'who',
            'why', 'how', 'all', 'each', 'every', 'both', 'few', 'more',
            'most', 'other', 'some', 'such', 'no', 'nor', 'not', 'only',
            'own', 'same', 'so', 'than', 'too', 'very', 'just', 'can',
            'lol', 'rt', 'gt', 'lt', 'ha', 'haha', 'hahaha', 'ok', 'oui',
            'non', 'ouais', 'bof', 'meh', 'pfff', 'pfff', 'zut', 'bah',
            '..', '...', '....', 'test', 'test2', 'test3', 'a', 'b', 'c'
        ];

        // ── 1. KEYWORD EXTRACTION ──
        $keywords = [];
        $publications = $this->pubRepo->findRecentActiveWithAuthorAndGroup(50);
        
        foreach ($publications as $pub) {
            if ($pub->getStatut() === 'SUPPRIME') continue;
            
            $contenu = strtolower($pub->getContenu());
            // Remove punctuation
            $contenu = preg_replace('/[^a-zàâäéèêëïîôöœùûüçñ\s0-9]/u', '', $contenu);
            // Split into words
            $words = preg_split('/\s+/', $contenu, -1, PREG_SPLIT_NO_EMPTY);
            
            foreach ($words as $word) {
                $word = trim($word);
                // Skip short words and stop words
                if (strlen($word) < 3 || in_array($word, $stopWords)) {
                    continue;
                }
                
                if (!isset($keywords[$word])) {
                    $keywords[$word] = 0;
                }
                $keywords[$word]++;
            }
        }

        // ── 2. DEPARTMENT-BASED TRENDING ──
        $departments = [];
        foreach ($publications as $pub) {
            if ($pub->getStatut() === 'SUPPRIME') continue;

            $author = $pub->getAuthorId();
            if ($author === null) {
                continue;
            }

            $dept = $author->getDepartement();
            if ($dept) {
                $deptName = strtolower(trim($dept));
                if (!isset($departments[$deptName])) {
                    $departments[$deptName] = 0;
                }
                $departments[$deptName]++;
            }
        }

        // ── 3. GROUP-BASED TRENDING ──
        $groups = [];
        foreach ($publications as $pub) {
            if ($pub->getStatut() === 'SUPPRIME') continue;
            
            $group = $pub->getGroupId();
            if ($group) {
                $groupName = strtolower(trim($group->getName()));
                if (!isset($groups[$groupName])) {
                    $groups[$groupName] = 0;
                }
                $groups[$groupName]++;
            }
        }

        // ── 3. COMBINE & WEIGHT ──
        // Keywords get more weight (they're more granular)
        $combined = [];
        
        foreach ($keywords as $keyword => $count) {
            $weight = $count * 1.5; // Keywords weighted 1.5x
            $combined[$keyword] = [
                'name' => $keyword,
                'count' => $count,
                'weight' => $weight,
                'type' => 'keyword',
            ];
        }
        
        foreach ($departments as $dept => $count) {
            $weight = $count * 1.0;
            $combined[$dept] = [
                'name' => $dept,
                'count' => $count,
                'weight' => $weight,
                'type' => 'department',
            ];
        }

        foreach ($groups as $group => $count) {
            $weight = $count * 1.0;
            $combined[$group] = [
                'name' => $group,
                'count' => $count,
                'weight' => $weight,
                'type' => 'group',
            ];
        }

        // Sort by count descending (highest first)
        usort($combined, function($a, $b) {
            return $b['count'] <=> $a['count'];
        });

        // Return top N
        $trending = [];
        foreach (array_slice($combined, 0, $limit) as $item) {
            $trending[] = [
                'name' => ucfirst($item['name']),
                'count' => $item['count'],
                'type' => $item['type'],
            ];
        }

        return $trending;
    }
}
