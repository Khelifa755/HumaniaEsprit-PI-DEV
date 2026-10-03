<?php

namespace App\Repository\SOCIALMEDIA;

use App\Entity\Share;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ShareRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Share::class);
    }

    public function findByUserAndPublication(int $userId, int $publicationId): ?Share
    {
        return $this->findOneBy(['userId' => $userId, 'publicationId' => $publicationId]);
    }

    public function findByPublicationId(int $publicationId)
    {
        return $this->findBy(['publicationId' => $publicationId], ['sharedAt' => 'DESC']);
    }

    public function findByUserId(int $userId)
    {
        return $this->findBy(['userId' => $userId], ['sharedAt' => 'DESC']);
    }

    public function countByPublicationId(int $publicationId): int
    {
        return $this->count(['publicationId' => $publicationId]);
    }

    /**
     * @param int[] $publicationIds
     * @return array<int, int> publicationId => nombre de partages
     */
    public function countGroupedByPublicationIds(array $publicationIds): array
    {
        $publicationIds = array_values(array_unique(array_map('intval', $publicationIds)));
        if ($publicationIds === []) {
            return [];
        }

        $rows = $this->createQueryBuilder('s')
            ->select('IDENTITY(s.publicationId) AS pubId', 'COUNT(s.id) AS cnt')
            ->where('IDENTITY(s.publicationId) IN (:ids)')
            ->setParameter('ids', $publicationIds)
            ->groupBy('s.publicationId')
            ->getQuery()
            ->getArrayResult();

        $out = [];
        foreach ($publicationIds as $id) {
            $out[$id] = 0;
        }
        foreach ($rows as $row) {
            $pid = (int) ($row['pubId'] ?? 0);
            if (isset($out[$pid])) {
                $out[$pid] = (int) $row['cnt'];
            }
        }

        return $out;
    }

    /**
     * @param int[] $publicationIds
     * @return int[] ids de publications que l'utilisateur a partagées
     */
    public function findPublicationIdsSharedByUser(int $userId, array $publicationIds): array
    {
        $publicationIds = array_values(array_unique(array_map('intval', $publicationIds)));
        if ($publicationIds === []) {
            return [];
        }

        $rows = $this->createQueryBuilder('s')
            ->select('IDENTITY(s.publicationId) AS pid')
            ->where('IDENTITY(s.userId) = :uid')
            ->andWhere('IDENTITY(s.publicationId) IN (:pids)')
            ->setParameter('uid', $userId)
            ->setParameter('pids', $publicationIds)
            ->getQuery()
            ->getArrayResult();

        $ids = [];
        foreach ($rows as $row) {
            $ids[] = (int) $row['pid'];
        }

        return array_values(array_unique($ids));
    }

    public function findAllOrdered(): array
    {
        return $this->findBy([], ['sharedAt' => 'DESC']);
    }

    public function incrementShareCount(int $shareId): void
    {
        $this->createQueryBuilder('s')
            ->update()
            ->set('s.shareCount', 's.shareCount + 1')
            ->where('s.id = :shareId')
            ->setParameter('shareId', $shareId)
            ->getQuery()
            ->execute();
    }
}
