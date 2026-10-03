<?php

namespace App\Repository\SOCIALMEDIA;

use App\Entity\Notification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Notification>
 */
class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    /**
     * Trouver toutes les notifications d'un utilisateur
     */
    public function findByUserId(int $userId, int $limit = 50): array
    {
        return $this->createQueryBuilder('n')
            ->andWhere('IDENTITY(n.userId) = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('n.dateCreation', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Compter les notifications non lues d'un utilisateur
     */
    public function countUnread(int $userId): int
    {
        return (int) $this->createQueryBuilder('n')
            ->select('COUNT(n.id)')
            ->andWhere('IDENTITY(n.userId) = :userId')
            ->andWhere('n.seen = false')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Récupérer les notifications non lues
     */
    public function findUnreadByUserId(int $userId, int $limit = 20): array
    {
        return $this->createQueryBuilder('n')
            ->andWhere('IDENTITY(n.userId) = :userId')
            ->andWhere('n.seen = false')
            ->setParameter('userId', $userId)
            ->orderBy('n.dateCreation', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Marquer une notification comme lue
     */
    public function markAsRead(Notification $notification): void
    {
        if (!$notification->getSeen()) {
            $notification->setSeen(true);
            $notification->setDateViewAt(new \DateTime());
            
            $this->getEntityManager()->persist($notification);
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Marquer toutes les notifications de l'utilisateur comme lues
     */
    public function markAllAsRead(int $userId): void
    {
        $this->createQueryBuilder('n')
            ->update()
            ->set('n.seen', 'true')
            ->set('n.dateViewAt', ':now')
            ->andWhere('IDENTITY(n.userId) = :userId')
            ->setParameter('userId', $userId)
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->execute();
    }

    /**
     * Créer une notification
     */
    public function createNotification(
        int $userId,
        string $type,
        string $titre,
        string $message,
        ?int $relatedUserId = null,
        ?int $relatedPublicationId = null,
        ?int $relatedCommentaireId = null
    ): Notification
    {
        $notification = new Notification();
        $notification->setUserId($this->getEntityManager()->getReference(\App\Entity\Users::class, $userId));
        $notification->setType($type);
        $notification->setTitre($titre);
        $notification->setMessage($message);
        $notification->setDateCreation(new \DateTime());
        $notification->setDateViewAt(null);
        $notification->setSeen(false);

        if ($relatedUserId) {
            $notification->setRelatedUserId($this->getEntityManager()->getReference(\App\Entity\Users::class, $relatedUserId));
        }

        $this->getEntityManager()->persist($notification);
        $this->getEntityManager()->flush();

        return $notification;
    }
}
