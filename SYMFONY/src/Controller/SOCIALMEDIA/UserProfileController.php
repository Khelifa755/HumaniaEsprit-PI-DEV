<?php

namespace App\Controller\SOCIALMEDIA;

use App\Repository\SOCIALMEDIA\UserprofileRepository;
use App\Repository\SOCIALMEDIA\PublicationRepository;
use App\Repository\SOCIALMEDIA\UserRepository;
use App\Repository\SOCIALMEDIA\SavedpostRepository;
use App\Entity\Savedpost;
use App\Entity\Follow;
use App\Entity\Groups;
use App\Entity\Groupmember;
use App\Entity\Userprofile;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[Route('/social/profile', name: 'social_profile_')]
class UserProfileController extends AbstractController
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
        private UserprofileRepository $profileRepo,
        private PublicationRepository $pubRepo,
        private SavedpostRepository $savedRepo,
        private \App\Repository\SOCIALMEDIA\NotificationRepository $notifRepo,
    ) {}

    /**
     * Mon profil
     * GET /social/profile/me
     */
    #[Route('/me', name: 'me', methods: ['GET'])]
    public function me(): Response
    {
        $currentUserId = $this->getCurrentUserId();
        $user = $this->em->getRepository(\App\Entity\Users::class)->find($currentUserId);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        $profile = $this->profileRepo->findOrCreate($user);
        $publications = $this->pubRepo->findByAuthor($currentUserId);

        // Calculate actual stats
        $stats = $this->calculateProfileStats($user, $publications);

        // ── SIDEBAR DATA ──
        $userSavedCount = count($this->savedRepo->findByUser($currentUserId));
        $followersCount = $this->em->createQueryBuilder()
            ->select('COUNT(f.id)')->from(Follow::class, 'f')
            ->where('f.followingId = :userId')->andWhere("f.status = 'ACTIVE'")
            ->setParameter('userId', $currentUserId)->getQuery()->getSingleScalarResult();
        $followingCount = $this->em->createQueryBuilder()
            ->select('COUNT(f.id)')->from(Follow::class, 'f')
            ->where('f.followerId = :userId')->andWhere("f.status = 'ACTIVE'")
            ->setParameter('userId', $currentUserId)->getQuery()->getSingleScalarResult();
        
        // Optimize: Batch query instead of loop with findOneBy
        $userGroups = $this->em->getRepository(Groups::class)->findAll();
        $userGroupsData = [];
        if (!empty($userGroups)) {
            $memberGroupIds = $this->em->createQueryBuilder()
                ->select('IDENTITY(m.groupId) AS gid')
                ->from(Groupmember::class, 'm')
                ->where('IDENTITY(m.userId) = :userId')
                ->setParameter('userId', $user)
                ->getQuery()
                ->getArrayResult();
            
            $memberGroupIdSet = array_flip(array_column($memberGroupIds, 'gid'));
            foreach ($userGroups as $group) {
                if (isset($memberGroupIdSet[$group->getId()])) {
                    $userGroupsData[] = $group;
                }
            }
        }
        
        $userProfile = $this->em->getRepository(Userprofile::class)->findOneBy(['userId' => $user]);

        return $this->render('SOCIALMEDIA/profile/me.html.twig', [
            'profile' => $profile,
            'user' => $user,
            'publications' => $publications,
            'stats' => $stats,
            'isOwner' => true,
            'notifications' => $this->notifRepo->findByUserId($currentUserId, 50),
            'userSavedCount' => $userSavedCount,
            'userGroups' => $userGroupsData,
            'followersCount' => $followersCount,
            'followingCount' => $followingCount,
            'eventsCount' => 0,
            'birthdaysCount' => 0,
            'userProfile' => $userProfile,
        ]);
    }

    /**
     * Profil public d'un utilisateur
     * GET /social/profile/{id}
     */
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): Response
    {
        $currentUserId = $this->getCurrentUserId();
        $user = $this->em->getRepository(\App\Entity\Users::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        $profile = $this->profileRepo->findOrCreate($user);
        $publications = $this->pubRepo->findByAuthor($id);

        // Calculate actual stats
        $stats = $this->calculateProfileStats($user, $publications);

        // ── SIDEBAR DATA ──
        $userSavedCount = count($this->savedRepo->findByUser($id));
        $followersCount = $this->em->createQueryBuilder()
            ->select('COUNT(f.id)')->from(Follow::class, 'f')
            ->where('f.followingId = :userId')->andWhere("f.status = 'ACTIVE'")
            ->setParameter('userId', $id)->getQuery()->getSingleScalarResult();
        $followingCount = $this->em->createQueryBuilder()
            ->select('COUNT(f.id)')->from(Follow::class, 'f')
            ->where('f.followerId = :userId')->andWhere("f.status = 'ACTIVE'")
            ->setParameter('userId', $id)->getQuery()->getSingleScalarResult();
        
        // Optimize: Batch query instead of loop with findOneBy
        $userGroups = $this->em->getRepository(Groups::class)->findAll();
        $userGroupsData = [];
        if (!empty($userGroups)) {
            $memberGroupIds = $this->em->createQueryBuilder()
                ->select('IDENTITY(m.groupId) AS gid')
                ->from(Groupmember::class, 'm')
                ->where('IDENTITY(m.userId) = :userId')
                ->setParameter('userId', $user)
                ->getQuery()
                ->getArrayResult();
            
            $memberGroupIdSet = array_flip(array_column($memberGroupIds, 'gid'));
            foreach ($userGroups as $group) {
                if (isset($memberGroupIdSet[$group->getId()])) {
                    $userGroupsData[] = $group;
                }
            }
        }
        
        $userProfile = $this->em->getRepository(Userprofile::class)->findOneBy(['userId' => $user]);

        return $this->render('SOCIALMEDIA/profile/show.html.twig', [
            'profile' => $profile,
            'user' => $user,
            'publications' => $publications,
            'stats' => $stats,
            'isOwner' => $id === $currentUserId,
            'notifications' => $this->notifRepo->findByUserId($currentUserId, 50),
            'userSavedCount' => $userSavedCount,
            'userGroups' => $userGroupsData,
            'followersCount' => $followersCount,
            'followingCount' => $followingCount,
            'eventsCount' => 0,
            'birthdaysCount' => 0,
            'userProfile' => $userProfile,
        ]);
    }

    /**
     * Mettre à jour la bio
     * POST /social/profile/update-bio
     */
    #[Route('/update-bio', name: 'update_bio', methods: ['POST'])]
    public function updateBio(Request $request): JsonResponse
    {
        $currentUserId = $this->getCurrentUserId();
        $bio = $request->request->get('bio', '');

        $this->profileRepo->updateBio($currentUserId, $bio);

        return $this->json(['success' => true, 'bio' => $bio]);
    }

    /**
     * Mettre à jour l'avatar
     * POST /social/profile/update-avatar
     */
    #[Route('/update-avatar', name: 'update_avatar', methods: ['POST'])]
    public function updateAvatar(Request $request): JsonResponse
    {
        $currentUserId = $this->getCurrentUserId();
        $avatarUrl = $request->request->get('avatarUrl', '');

        // Update userprofile
        $this->profileRepo->updateAvatarUrl($currentUserId, $avatarUrl);

        // Sync vers utilisateur.pdp
        $user = $this->em->getRepository(\App\Entity\Users::class)->find($currentUserId);
        if ($user) {
            $user->setAvatarUrl($avatarUrl);
            $this->em->flush();
        }

        return $this->json(['success' => true, 'avatarUrl' => $avatarUrl]);
    }

    /**
     * Mettre à jour la cover
     * POST /social/profile/update-cover
     */
    #[Route('/update-cover', name: 'update_cover', methods: ['POST'])]
    public function updateCover(Request $request): JsonResponse
    {
        $currentUserId = $this->getCurrentUserId();
        $coverUrl = $request->request->get('coverUrl', '');

        $this->profileRepo->updateCoverUrl($currentUserId, $coverUrl);

        return $this->json(['success' => true, 'coverUrl' => $coverUrl]);
    }

    /**
     * Upload avatar (fichier)
     * POST /social/profile/upload-avatar
     */
    #[Route('/upload-avatar', name: 'upload_avatar', methods: ['POST'])]
    public function uploadAvatar(Request $request): JsonResponse
    {
        $currentUserId = $this->getCurrentUserId();
        
        /** @var UploadedFile|null $file */
        $file = $request->files->get('file');

        if (!$file) {
            return $this->json(['error' => 'Aucun fichier fourni'], 400);
        }

        // Validations
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file->getMimeType(), $allowedMimes)) {
            return $this->json(['error' => 'Format d\'image non supporté'], 400);
        }

        if ($file->getSize() > 5 * 1024 * 1024) { // 5MB max
            return $this->json(['error' => 'Fichier trop volumineux (max 5MB)'], 400);
        }

        try {
            // Créer le répertoire de destination s'il n'existe pas
            $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/profiles/avatars';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Générer un nom de fichier unique
            $filename = 'avatar_' . $currentUserId . '_' . time() . '.' . $file->guessExtension();
            $file->move($uploadDir, $filename);

            // Générer l'URL relative
            $avatarUrl = '/uploads/profiles/avatars/' . $filename;

            // Mettre à jour la BD
            $this->profileRepo->updateAvatarUrl($currentUserId, $avatarUrl);
            $user = $this->em->getRepository(\App\Entity\Users::class)->find($currentUserId);
            if ($user) {
                $user->setAvatarUrl($avatarUrl);
                $this->em->flush();
            }

            return $this->json(['success' => true, 'avatarUrl' => $avatarUrl, 'message' => 'Avatar mis à jour']);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Erreur lors du téléchargement: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Upload cover (fichier)
     * POST /social/profile/upload-cover
     */
    #[Route('/upload-cover', name: 'upload_cover', methods: ['POST'])]
    public function uploadCover(Request $request): JsonResponse
    {
        $currentUserId = $this->getCurrentUserId();
        
        /** @var UploadedFile|null $file */
        $file = $request->files->get('file');

        if (!$file) {
            return $this->json(['error' => 'Aucun fichier fourni'], 400);
        }

        // Validations
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file->getMimeType(), $allowedMimes)) {
            return $this->json(['error' => 'Format d\'image non supporté'], 400);
        }

        if ($file->getSize() > 10 * 1024 * 1024) { // 10MB max pour la cover
            return $this->json(['error' => 'Fichier trop volumineux (max 10MB)'], 400);
        }

        try {
            // Créer le répertoire de destination s'il n'existe pas
            $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/profiles/covers';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Générer un nom de fichier unique
            $filename = 'cover_' . $currentUserId . '_' . time() . '.' . $file->guessExtension();
            $file->move($uploadDir, $filename);

            // Générer l'URL relative
            $coverUrl = '/uploads/profiles/covers/' . $filename;

            // Mettre à jour la BD
            $this->profileRepo->updateCoverUrl($currentUserId, $coverUrl);

            return $this->json(['success' => true, 'coverUrl' => $coverUrl, 'message' => 'Cover mise à jour']);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Erreur lors du téléchargement: ' . $e->getMessage()], 500);
        }
    }

    /**
     * API — données du profil en JSON
     * GET /social/profile/api/{id}
     */
    #[Route('/api/{id}', name: 'api_show', methods: ['GET'])]
    public function apiShow(int $id): JsonResponse
    {
        $user = $this->em->getRepository(\App\Entity\Users::class)->find($id);

        if (!$user) {
            return $this->json(['error' => 'Utilisateur non trouvé'], 404);
        }

        $profile = $this->profileRepo->findOrCreate($user);

        return $this->json([
            'id' => $user->getId(),
            'fullName' => $profile->getFullName(),
            'username' => $profile->getUsername(),
            'avatarUrl' => $profile->getAvatarUrl(),
            'coverUrl' => $profile->getCoverUrl(),
            'bio' => $profile->getBio(),
            'jobTitle' => $profile->getJobTitle(),
            'company' => $profile->getCompany(),
            'followersCount' => $profile->getFollowersCount(),
            'followingCount' => $profile->getFollowingCount(),
            'postsCount' => $profile->getPostsCount(),
            'isOnline' => $profile->isOnline(),
            'initials' => $profile->getInitials(),
        ]);
    }

    /**
     * Calculate profile statistics dynamically
     */
    private function calculateProfileStats(\App\Entity\Users $user, array $publications): array
    {
        // Publications count
        $postsCount = count($publications);

        // Followers count (users who follow this user)
        $qb = $this->em->createQueryBuilder();
        $followersCount = $qb
            ->select('COUNT(f.id)')
            ->from(\App\Entity\Follow::class, 'f')
            ->where('f.followingId = :userId')
            ->andWhere("f.status = 'ACTIVE'")
            ->setParameter('userId', $user->getId())
            ->getQuery()
            ->getSingleScalarResult();

        // Following count (users this person follows)
        $qb = $this->em->createQueryBuilder();
        $followingCount = $qb
            ->select('COUNT(f.id)')
            ->from(\App\Entity\Follow::class, 'f')
            ->where('f.followerId = :userId')
            ->andWhere("f.status = 'ACTIVE'")
            ->setParameter('userId', $user->getId())
            ->getQuery()
            ->getSingleScalarResult();

        return [
            'postsCount' => $postsCount,
            'followersCount' => $followersCount,
            'followingCount' => $followingCount,
        ];
    }
}
