<?php

namespace App\Repository\PLANIFICATION;

use App\Entity\Coworking_reservations;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Java equivalent: raw SQL inside CoworkingReservationService
 *
 * The three query patterns from the Java service are reproduced here:
 *   1. getReservationsForDate  → findByEspaceAndDate()
 *   2. reserveChair (lock+insert in tx) → insertWithLock()
 *   3. cancelChair             → deleteChair()
 *   4. cancelAllForUser        → deleteAllForUser()
 */
class Coworking_reservationsRepository extends ServiceEntityRepository
{
    private Connection $dbal;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Coworking_reservations::class);
        // Raw DBAL connection kept for the FOR UPDATE locking query
        // (Doctrine ORM pessimistic lock requires an existing managed entity,
        //  but we need to lock a row that may not exist yet — same as Java).
        $this->dbal = $registry->getConnection();
    }

    // ────────────────────────────────────────────────────────────────────────
    // Java: getReservationsForDate(int espaceId, Date day)
    // ────────────────────────────────────────────────────────────────────────

    /**
     * Returns all Coworking_reservations objects for a given espace and date.
     *
     * Java SQL:
     *   SELECT id, espace_id, chair_number, user_id, reservation_date, created_at
     *   FROM coworking_reservations WHERE espace_id = ? AND reservation_date = ?
     */
    public function findByEspaceAndDate(int $espaceId, \DateTimeInterface $day): array
    {
        $dayParam = $day instanceof \DateTimeImmutable
            ? $day
            : \DateTimeImmutable::createFromInterface($day);

        return $this->createQueryBuilder('r')
            ->where('r.espaceId = :eid')
            ->andWhere('r.reservationDate = :day')
            ->setParameter('eid', $espaceId)
            ->setParameter('day', $dayParam, Types::DATE_IMMUTABLE)
            ->getQuery()
            ->getResult();
    }

    // ────────────────────────────────────────────────────────────────────────
    // Java: reserveChair (transaction + FOR UPDATE + INSERT)
    // ────────────────────────────────────────────────────────────────────────

    /**
     * Attempts to reserve a chair using a database transaction with a
     * SELECT … FOR UPDATE lock — exactly replicating the Java logic.
     *
     * Java lock SQL:
     *   SELECT id, user_id FROM coworking_reservations
     *   WHERE espace_id = ? AND chair_number = ? AND reservation_date = ? FOR UPDATE
     *
     * Java insert SQL:
     *   INSERT INTO coworking_reservations
     *   (espace_id, chair_number, user_id, reservation_date, created_at)
     *   VALUES (?,?,?,?,?)
     *
     * @throws \RuntimeException  wrapping IllegalStateException if already booked
     *                            (mirrors Java's throw inside the lock block)
     */
    public function insertWithLock(
        int $espaceId,
        int $chairNumber,
        int $userId,
        \DateTimeInterface $day
    ): void {
        $conn = $this->dbal;
        $conn->beginTransaction();

        try {
            // Java: lockSql — FOR UPDATE prevents concurrent inserts on same row.
            $existing = $conn->fetchAssociative(
                'SELECT id, user_id FROM coworking_reservations
                 WHERE espace_id = ? AND chair_number = ? AND reservation_date = ?
                 FOR UPDATE',
                [$espaceId, $chairNumber, $day->format('Y-m-d')]
            );

            // Java: if (rs.next()) { throw new IllegalStateException(...) }
            if ($existing !== false) {
                throw new \RuntimeException('Cette chaise est déjà réservée pour cette date.');
            }

            // Java: insertSql
            $conn->executeStatement(
                'INSERT INTO coworking_reservations
                 (espace_id, chair_number, user_id, reservation_date, created_at)
                 VALUES (?, ?, ?, ?, ?)',
                [
                    $espaceId,
                    $chairNumber,
                    $userId,
                    $day->format('Y-m-d'),
                    (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
                ]
            );

            $conn->commit();
        } catch (\Throwable $e) {
            // Java: catch (Exception ex) { cnx.rollback(); throw new RuntimeException(...) }
            $conn->rollBack();
            throw new \RuntimeException(
                'Impossible de réserver la chaise ' . $chairNumber,
                0,
                $e
            );
        }
    }

    // ────────────────────────────────────────────────────────────────────────
    // Java: cancelChair(int espaceId, int chairNumber, int userId, Date day)
    // ────────────────────────────────────────────────────────────────────────

    /**
     * Java SQL:
     *   DELETE FROM coworking_reservations
     *   WHERE espace_id = ? AND chair_number = ? AND user_id = ? AND reservation_date = ?
     */
    public function deleteChair(
        int $espaceId,
        int $chairNumber,
        int $userId,
        \DateTimeInterface $day
    ): void {
        try {
            $this->dbal->executeStatement(
                'DELETE FROM coworking_reservations
                 WHERE espace_id = ? AND chair_number = ? AND user_id = ? AND reservation_date = ?',
                [$espaceId, $chairNumber, $userId, $day->format('Y-m-d')]
            );
        } catch (\Exception $e) {
            throw new \RuntimeException(
                'Impossible d\'annuler la réservation de la chaise ' . $chairNumber,
                0,
                $e
            );
        }
    }

    // ────────────────────────────────────────────────────────────────────────
    // Java: cancelAllForUser(int espaceId, int userId, Date day)
    // ────────────────────────────────────────────────────────────────────────

    /**
     * Java SQL:
     *   DELETE FROM coworking_reservations
     *   WHERE espace_id = ? AND user_id = ? AND reservation_date = ?
     */
    public function deleteAllForUser(int $espaceId, int $userId, \DateTimeInterface $day): void
    {
        try {
            $this->dbal->executeStatement(
                'DELETE FROM coworking_reservations
                 WHERE espace_id = ? AND user_id = ? AND reservation_date = ?',
                [$espaceId, $userId, $day->format('Y-m-d')]
            );
        } catch (\Exception $e) {
            throw new \RuntimeException(
                'Impossible d\'annuler les réservations de l\'utilisateur',
                0,
                $e
            );
        }
    }
}