<?php

namespace App\Repository\SOCIALMEDIA;

use App\Entity\Employe;
use App\Entity\Evenement;
use App\Entity\Publication;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

class PublicationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Publication::class);
    }

    /**
     * Feed principal : publications ACTIF publiques, sans groupe, triées par date
     */
    public function findFeed(int $limit = 20): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.authorId', 'u')
            ->addSelect('u')
            ->where('p.statut = :statut')
            ->andWhere('p.visibility = :visibility')
            ->andWhere('p.groupId IS NULL')
            ->setParameter('statut', 'ACTIF')
            ->setParameter('visibility', 'PUBLIC')
            ->orderBy('p.dateCreation', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
    

    /**
     * Compter les publications actives d'un user
     */
    public function countByUser(int $userId): int
    {
        return (int) $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.authorId = :userId')
            ->andWhere('p.statut = :statut')
            ->setParameter('userId', $userId)
            ->setParameter('statut', 'ACTIF')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Publications populaires : triées par nombreReactions DESC
     */
    public function findPopular(int $limit = 20): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.authorId', 'u')
            ->addSelect('u')
            ->where('p.statut = :statut')
            ->andWhere('p.visibility = :visibility')
            ->andWhere('p.groupId IS NULL')
            ->setParameter('statut', 'ACTIF')
            ->setParameter('visibility', 'PUBLIC')
            ->orderBy('p.nombreReactions', 'DESC')
            ->addOrderBy('p.dateCreation', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Publications sauvegardées par un utilisateur
     */
    public function findSavedByUser(int $userId, int $limit = 20): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.authorId', 'u')
            ->addSelect('u')
            ->innerJoin('App\Entity\Savedpost', 's', 'WITH', 's.publicationId = p AND IDENTITY(s.userId) = :userId')
            ->where('p.statut = :statut')
            ->setParameter('userId', $userId)
            ->setParameter('statut', 'ACTIF')
            ->orderBy('s.savedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findByAuthor(int $userId, int $limit = 20): array
{
    return $this->createQueryBuilder('p')
        ->leftJoin('p.authorId', 'u')
        ->addSelect('u')
        ->where('IDENTITY(p.authorId) = :userId')
        ->andWhere('p.statut = :statut')
        ->setParameter('userId', $userId)
        ->setParameter('statut', 'ACTIF')
        ->orderBy('p.dateCreation', 'DESC')
        ->setMaxResults($limit)
        ->getQuery()
        ->getResult();
}

    public function existsByEmployeeAndType(Employe $employee, string $type): bool
    {
        return (bool) $this->createQueryBuilder('p')
            ->select('1')
            ->where('p.relatedEmployee = :employee')
            ->andWhere('p.type = :type')
            ->setParameter('employee', $employee)
            ->setParameter('type', $type)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function existsByEvent(Evenement $event): bool
    {
        return (bool) $this->createQueryBuilder('p')
            ->select('1')
            ->where('p.relatedEvent = :event')
            ->setParameter('event', $event)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
    /**
     * Publications récentes non supprimées (pour tendances / stats texte), avec auteur et groupe.
     * Limited to 100 rows instead of 300 to avoid excessive hydration overhead.
     */
    
    public function findRecentActiveWithAuthorAndGroup(int $limit = 100): array
    {
        return $this->createQueryBuilder('p')
            ->select('p', 'u', 'g')
            ->leftJoin('p.authorId', 'u')
            ->addSelect('u')
            ->leftJoin('p.groupId', 'g')
            ->addSelect('g')
            ->where('p.statut != :suppr')
            ->setParameter('suppr', 'SUPPRIME')
            ->orderBy('p.dateCreation', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
