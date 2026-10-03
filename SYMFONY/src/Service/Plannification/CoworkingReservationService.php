<?php

namespace App\Service\Plannification;

use App\Entity\Coworking_reservations;
use App\Model\Plannification\ChairViewModel;
use App\Repository\PLANIFICATION\Coworking_reservationsRepository;

/**
 * Java equivalent: CoworkingReservationService
 *
 * Business-logic layer. Every public method maps 1-to-1 to a Java method;
 * the same rules, constraints, and error messages are preserved.
 *
 * Additionally provides buildChairGrid() which reproduces the JavaFX
 * controller logic that converts a list of reservations into the
 * ChairModel (→ ChairViewModel) list displayed in the seat-picker UI.
 */
class CoworkingReservationService
{
    public function __construct(
        private readonly Coworking_reservationsRepository $repo
    ) {}

    // ── Java: getReservationsForDate ──────────────────────────────────────────

    /**
     * Returns all reservations for a given espace and date.
     *
     * @return Coworking_reservations[]
     */
    public function getReservationsForDate(int $espaceId, \DateTimeInterface $day): array
    {
        return $this->repo->findByEspaceAndDate($espaceId, $day);
    }

    // ── Java: reserveChair ────────────────────────────────────────────────────

    /**
     * Attempts to reserve a single chair for a user and date.
     *
     * Delegates directly to the repository which replicates the Java
     * transaction + FOR UPDATE lock pattern verbatim.
     *
     * @throws \RuntimeException if the chair is already taken
     *         (mirrors Java's IllegalStateException → RuntimeException chain)
     */
    public function reserveChair(
        int $espaceId,
        int $chairNumber,
        int $userId,
        \DateTimeInterface $day
    ): void {
        $this->repo->insertWithLock($espaceId, $chairNumber, $userId, $day);
    }

    // ── Java: cancelChair ─────────────────────────────────────────────────────

    /**
     * Cancels a specific chair reservation for the given user and date.
     *
     * @throws \RuntimeException on DB error
     */
    public function cancelChair(
        int $espaceId,
        int $chairNumber,
        int $userId,
        \DateTimeInterface $day
    ): void {
        $this->repo->deleteChair($espaceId, $chairNumber, $userId, $day);
    }

    // ── Java: cancelAllForUser ────────────────────────────────────────────────

    /**
     * Cancels ALL reservations for a user within an espace on a given date.
     *
     * @throws \RuntimeException on DB error
     */
    public function cancelAllForUser(int $espaceId, int $userId, \DateTimeInterface $day): void
    {
        $this->repo->deleteAllForUser($espaceId, $userId, $day);
    }

    // ── JavaFX controller logic: build seat-grid ──────────────────────────────

    /**
     * Builds the full list of ChairViewModel objects for the seat-picker UI.
     *
     * Java equivalent: the JavaFX controller loop that iterated over
     * ObservableList<ChairModel> and assigned State.MINE / AVAILABLE / TAKEN
     * based on comparing reservation.getUserId() with the current user id.
     *
     * Rules (identical to Java):
     *   - Seat numbers go from 1 to $capacite (matches Espace.getCapacite()).
     *   - If a reservation exists for the seat:
     *       → userId matches current user  → STATE_MINE
     *       → different userId             → STATE_TAKEN
     *   - No reservation for the seat     → STATE_AVAILABLE
     *
     * @param Coworking_reservations[] $reservations  result of getReservationsForDate()
     * @return ChairViewModel[]
     */
    public function buildChairGrid(
        int $capacite,
        array $reservations,
        int $currentUserId
    ): array {
        // Index reservations by chair_number for O(1) lookup
        $byChair = [];
        foreach ($reservations as $r) {
            $byChair[$r->getChairNumber()] = $r;
        }

        $chairs = [];
        for ($seat = 1; $seat <= $capacite; $seat++) {
            if (!isset($byChair[$seat])) {
                // Java: State.AVAILABLE
                $chairs[] = new ChairViewModel($seat, ChairViewModel::STATE_AVAILABLE);
            } elseif ($byChair[$seat]->getUserId() === $currentUserId) {
                // Java: State.MINE
                $chairs[] = new ChairViewModel(
                    $seat,
                    ChairViewModel::STATE_MINE,
                    $currentUserId
                );
            } else {
                // Java: State.TAKEN
                $chairs[] = new ChairViewModel(
                    $seat,
                    ChairViewModel::STATE_TAKEN,
                    $byChair[$seat]->getUserId()
                );
            }
        }

        return $chairs;
    }
}