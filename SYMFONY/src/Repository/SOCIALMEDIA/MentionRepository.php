<?php

namespace App\Repository\SOCIALMEDIA;

use App\Entity\Mention;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MentionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Mention::class);
    }

    /**
     * Find mentions for a specific publication
     */
    public function findByPublication(int $publicationId): array
    {
        return $this->createQueryBuilder('m')
            ->where('IDENTITY(m.publicationId) = :pubId')
            ->setParameter('pubId', $publicationId)
            ->orderBy('m.createdAt', 'DESC')
            ->setMaxResults(1000)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find mentions for a specific comment
     */
    public function findByCommentaire(int $commentaireId): array
    {
        return $this->createQueryBuilder('m')
            ->where('IDENTITY(m.commentaireId) = :commentId')
            ->setParameter('commentId', $commentaireId)
            ->orderBy('m.createdAt', 'DESC')
            ->setMaxResults(1000)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find unread mentions for a user
     */
    public function findUnreadByUser(int $userId): array
    {
        return $this->createQueryBuilder('m')
            ->where('IDENTITY(m.mentionedUserId) = :userId')
            ->andWhere('m.status = :status')
            ->setParameter('userId', $userId)
            ->setParameter('status', 'UNREAD')
            ->orderBy('m.createdAt', 'DESC')
            ->setMaxResults(50)
            ->getQuery()
            ->getResult();
    }

    /**
     * Count unread mentions for a user
     */
    public function countUnreadByUser(int $userId): int
    {
        return (int) $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->where('IDENTITY(m.mentionedUserId) = :userId')
            ->andWhere('m.status = :status')
            ->setParameter('userId', $userId)
            ->setParameter('status', 'UNREAD')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Mark mentions as read
     */
    public function markAsRead(int $userId): void
    {
        $this->createQueryBuilder('m')
            ->update()
            ->set('m.status', ':status')
            ->where('IDENTITY(m.mentionedUserId) = :userId')
            ->andWhere('m.status = :oldStatus')
            ->setParameter('status', 'READ')
            ->setParameter('userId', $userId)
            ->setParameter('oldStatus', 'UNREAD')
            ->getQuery()
            ->execute();
    }
}
