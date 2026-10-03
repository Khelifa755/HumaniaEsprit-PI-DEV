<?php

namespace App\Repository\SOCIALMEDIA;

use App\Entity\Reaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ReactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reaction::class);
    }

    /**
     * Réaction d'un user sur une publication (UNIQUE userId+publicationId)
     */
    public function findByUserAndPublication(int $userId, int $publicationId): ?Reaction
    {
        return $this->createQueryBuilder('r')
            ->where('r.userId = :userId')
            ->andWhere('r.publicationId = :pubId')
            ->setParameter('userId', $userId)
            ->setParameter('pubId', $publicationId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Compter les réactions par type pour une publication
     */
    public function countByType(int $publicationId): array
    {
        $results = $this->createQueryBuilder('r')
            ->select('r.type, COUNT(r.id) as count')
            ->where('r.publicationId = :pubId')
            ->setParameter('pubId', $publicationId)
            ->groupBy('r.type')
            ->orderBy('count', 'DESC')
            ->getQuery()
            ->getResult();

        $counts = [];
        foreach ($results as $row) {
            $counts[$row['type']] = $row['count'];
        }
        return $counts;
    }

    /**
     * Compteurs par type et par publication en une requête (évite N+1 sur le fil de groupe).
     *
     * @param int[] $publicationIds
     * @return array<int, array<string, int>> publicationId => [ type => count ]
     */
    public function aggregateCountsByPublicationIds(array $publicationIds): array
    {
        $publicationIds = array_values(array_unique(array_map('intval', $publicationIds)));
        if ($publicationIds === []) {
            return [];
        }

        $rows = $this->createQueryBuilder('r')
            ->select('IDENTITY(r.publicationId) AS pubId', 'r.type', 'COUNT(r.id) AS cnt')
            ->where('IDENTITY(r.publicationId) IN (:ids)')
            ->andWhere('r.publicationId IS NOT NULL')
            ->setParameter('ids', $publicationIds)
            ->groupBy('r.publicationId')
            ->addGroupBy('r.type')
            ->getQuery()
            ->getArrayResult();

        $out = [];
        foreach ($publicationIds as $id) {
            $out[$id] = [];
        }
        foreach ($rows as $row) {
            $pid = (int) ($row['pubId'] ?? 0);
            if (!isset($out[$pid])) {
                $out[$pid] = [];
            }
            $out[$pid][$row['type']] = (int) $row['cnt'];
        }

        return $out;
    }

    /**
     * Réaction courante de l'utilisateur sur un ensemble de publications (1 requête).
     *
     * @param int[] $publicationIds
     * @return array<int, string|null> publicationId => type ou null
     */
    public function findTypesByUserAndPublicationIds(int $userId, array $publicationIds): array
    {
        $publicationIds = array_values(array_unique(array_map('intval', $publicationIds)));
        if ($publicationIds === []) {
            return [];
        }

        $rows = $this->createQueryBuilder('r')
            ->select('IDENTITY(r.publicationId) AS pubId', 'r.type')
            ->where('IDENTITY(r.userId) = :uid')
            ->andWhere('IDENTITY(r.publicationId) IN (:pids)')
            ->setParameter('uid', $userId)
            ->setParameter('pids', $publicationIds)
            ->getQuery()
            ->getArrayResult();

        $map = [];
        foreach ($publicationIds as $id) {
            $map[$id] = null;
        }
        foreach ($rows as $row) {
            $pid = (int) ($row['pubId'] ?? 0);
            if (array_key_exists($pid, $map)) {
                $map[$pid] = $row['type'];
            }
        }

        return $map;
    }
}
