<?php

namespace App\Repository\SOCIALMEDIA;

use App\Entity\Savedpost;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class SavedpostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Savedpost::class);
    }

    /**
     * Vérifier si un user a sauvegardé une publication
     */
    public function findByUserAndPublication(int $userId, int $publicationId): ?Savedpost
    {
        return $this->createQueryBuilder('s')
            ->where('s.userId = :userId')
            ->andWhere('s.publicationId = :pubId')
            ->setParameter('userId', $userId)
            ->setParameter('pubId', $publicationId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Toutes les publications sauvegardées d'un user
     */
    public function findByUser(int $userId, int $limit = 500): array
    {
        return $this->createQueryBuilder('s')
            ->where('IDENTITY(s.userId) = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('s.savedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countByUser(int $userId): int
    {
        return (int) $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->where('IDENTITY(s.userId) = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param int[] $publicationIds
     * @return int[] ids de publications sauvegardées par l'utilisateur
     */
    public function findPublicationIdsSavedByUser(int $userId, array $publicationIds): array
    {
        $publicationIds = array_values(array_unique(array_map('intval', $publicationIds)));
        if ($publicationIds === []) {
            return [];
        }

        $rows = $this->createQueryBuilder('s')
            ->select('IDENTITY(s.publicationId) AS pid')
            ->where('IDENTITY(s.userId) = :userId')
            ->andWhere('IDENTITY(s.publicationId) IN (:pids)')
            ->setParameter('userId', $userId)
            ->setParameter('pids', $publicationIds)
            ->getQuery()
            ->getArrayResult();

        $ids = [];
        foreach ($rows as $row) {
            $ids[] = (int) $row['pid'];
        }

        return array_values(array_unique($ids));
    }
}
