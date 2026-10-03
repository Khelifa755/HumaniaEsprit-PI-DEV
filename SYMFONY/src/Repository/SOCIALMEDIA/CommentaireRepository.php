<?php

namespace App\Repository\SOCIALMEDIA;

use App\Entity\Commentaire;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CommentaireRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commentaire::class);
    }

    /**
     * Commentaires ACTIF d'une publication, triés par date DESC (plus récent d'abord)
     */
    public function findByPublication(int $publicationId, int $limit = 100): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.authorId', 'u')
            
            ->where('c.publicationId = :pubId')
            ->andWhere('c.statut = :statut')
            ->setParameter('pubId', $publicationId)
            ->setParameter('statut', 'ACTIF')
            ->orderBy('c.dateCreation', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Tous les commentaires ACTIF pour plusieurs publications en une requête,
     * auteurs inclus (évite N+1 dans les boucles Twig).
     * 
     * Note: Limits total comments fetched to avoid excessive memory usage.
     * Comments are then distributed per publication.
     *
     * @param int[] $publicationIds
     * @return array<int, Commentaire[]> id publication => liste (max $maxPerPublication par pub, plus récents d'abord)
     */
public function findActiveByPublicationIdsGrouped(array $publicationIds, int $maxPerPublication = 3): array    {
        $publicationIds = array_values(array_unique(array_map('intval', $publicationIds)));
        if ($publicationIds === []) {
            return [];
        }

        $byPub = [];
        foreach ($publicationIds as $id) {
            $byPub[$id] = [];
        }

        // Limit total comments to avoid excessive hydration (max $maxPerPublication * number of publications)
        $totalLimit = min($maxPerPublication * \count($publicationIds), 30);
        
        $comments = $this->createQueryBuilder('c')
            ->leftJoin('c.authorId', 'u')
            ->addSelect('u')
            ->where('IDENTITY(c.publicationId) IN (:ids)')
            ->andWhere('c.statut = :statut')
            ->setParameter('ids', $publicationIds)
            ->setParameter('statut', 'ACTIF')
            ->orderBy('c.publicationId', 'ASC')
            ->addOrderBy('c.dateCreation', 'DESC')
            ->setMaxResults($totalLimit)
            ->getQuery()
            ->getResult();

        foreach ($comments as $comment) {
            $pid = $comment->getPublicationId()->getId();
            if (!isset($byPub[$pid])) {
                continue;
            }
            if (\count($byPub[$pid]) >= $maxPerPublication) {
                continue;
            }
            $byPub[$pid][] = $comment;
        }

        return $byPub;
    }
}
