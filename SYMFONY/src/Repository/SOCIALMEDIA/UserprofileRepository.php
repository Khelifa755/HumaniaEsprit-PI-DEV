<?php

namespace App\Repository\SOCIALMEDIA;

use App\Entity\Userprofile;
use App\Entity\Users;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UserprofileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Userprofile::class);
    }

    /**
     * Trouver le profil d'un utilisateur
     */
    public function findByUserId(int $userId): ?Userprofile
{
    return $this->createQueryBuilder('p')
        ->where('IDENTITY(p.userId) = :userId')
        ->setParameter('userId', $userId)
        ->getQuery()
        ->getOneOrNullResult();
}

    /**
     * Créer un profil vide si l'utilisateur n'en a pas encore
     */
    public function findOrCreate(Users $user): Userprofile
    {
        $profile = $this->findByUserId($user->getId());

        if (!$profile) {
            $profile = new Userprofile();
            $profile->setUserId($user);
            $profile->setFollowersCount(0);
            $profile->setFollowingCount(0);
            $profile->setPostsCount(0);

            $this->getEntityManager()->persist($profile);
            $this->getEntityManager()->flush();
        }

        return $profile;
    }

    /**
     * Mettre à jour la bio
     */
    public function updateBio(int $userId, string $bio): void
    {
        $this->createQueryBuilder('p')
            ->update()
            ->set('p.bio', ':bio')
            ->where('IDENTITY(p.userId) = :userId')
            ->setParameter('bio', $bio)
            ->setParameter('userId', $userId)
            ->getQuery()
            ->execute();
    }

    /**
     * Mettre à jour l'avatar
     */
    public function updateAvatarUrl(int $userId, string $avatarUrl): void
    {
        $this->createQueryBuilder('p')
            ->update()
            ->set('p.avatarUrl', ':avatarUrl')
            ->where('IDENTITY(p.userId) = :userId')
            ->setParameter('avatarUrl', $avatarUrl)
            ->setParameter('userId', $userId)
            ->getQuery()
            ->execute();
    }

    /**
     * Mettre à jour la cover
     */
    public function updateCoverUrl(int $userId, string $coverUrl): void
    {
        $this->createQueryBuilder('p')
            ->update()
            ->set('p.coverUrl', ':coverUrl')
            ->where('IDENTITY(p.userId) = :userId')
            ->setParameter('coverUrl', $coverUrl)
            ->setParameter('userId', $userId)
            ->getQuery()
            ->execute();
    }

    /**
     * Incrémenter/décrémenter les compteurs
     */
    public function incrementFollowersCount(int $userId): void
    {
        $this->updateCounter($userId, 'followersCount', '+1');
    }

    public function decrementFollowersCount(int $userId): void
    {
        $this->updateCounter($userId, 'followersCount', '-1');
    }

    public function incrementFollowingCount(int $userId): void
    {
        $this->updateCounter($userId, 'followingCount', '+1');
    }

    public function decrementFollowingCount(int $userId): void
    {
        $this->updateCounter($userId, 'followingCount', '-1');
    }

    public function incrementPostsCount(int $userId): void
    {
        $this->updateCounter($userId, 'postsCount', '+1');
    }

    public function decrementPostsCount(int $userId): void
    {
        $this->updateCounter($userId, 'postsCount', '-1');
    }

    private function updateCounter(int $userId, string $field, string $delta): void
    {
        $this->createQueryBuilder('p')
            ->update()
            ->set("p.$field", "GREATEST(p.$field $delta, 0)")
            ->where('IDENTITY(p.userId) = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->execute();
    }
}