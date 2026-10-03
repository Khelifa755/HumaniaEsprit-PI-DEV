<?php

namespace App\Repository\PLANIFICATION;

use App\Entity\Evenement;
use App\Entity\Participation_evenement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Participation_evenement>
 */
class ParticipationEvenementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Participation_evenement::class);
    }

    // ── All active (non-cancelled) participations for an employee ─────────────

    /**
     * @return Participation_evenement[]
     */
    public function findActiveByEmploye(int $idEmploye): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.idEmploye = :id')
            ->andWhere('p.statut != :annule')
            ->setParameter('id', $idEmploye)
            ->setParameter('annule', 'annule')
            ->getQuery()
            ->getResult();
    }

    // ── Single active participation for one employee + one event ──────────────

    public function findOneActiveByEmployeAndEvent(
        int $idEmploye,
        Evenement $evenement,
    ): ?Participation_evenement {
        return $this->createQueryBuilder('p')
            ->where('p.idEmploye = :id')
            ->andWhere('p.idEvenement = :ev')
            ->andWhere('p.statut != :annule')
            ->setParameter('id', $idEmploye)
            ->setParameter('ev', $evenement)
            ->setParameter('annule', 'annule')
            ->getQuery()
            ->getOneOrNullResult();
    }

    // ── Count confirmed participants for capacity check ────────────────────────

    public function countConfirmedByEvent(Evenement $evenement): int
    {
        return (int) $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.idEvenement = :ev')
            ->andWhere('p.statut != :annule')
            ->setParameter('ev', $evenement)
            ->setParameter('annule', 'annule')
            ->getQuery()
            ->getSingleScalarResult();
    }

    // ── Upcoming events (after now) ───────────────────────────────────────────
    // Used by the index to build the capacity map in one query.

    /**
     * Returns a map: event_id → confirmed_count  for all given event IDs.
     *
     * @param int[] $eventIds
     * @return array<int,int>
     */
    public function countsByEventIds(array $eventIds): array
    {
        if (empty($eventIds)) {
            return [];
        }

        $rows = $this->createQueryBuilder('p')
            ->select('IDENTITY(p.idEvenement) AS evId, COUNT(p.id) AS cnt')
            ->where('p.idEvenement IN (:ids)')
            ->andWhere('p.statut != :annule')
            ->setParameter('ids', $eventIds)
            ->setParameter('annule', 'annule')
            ->groupBy('p.idEvenement')
            ->getQuery()
            ->getScalarResult();

        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row['evId']] = (int) $row['cnt'];
        }

        return $map;
    }
}
