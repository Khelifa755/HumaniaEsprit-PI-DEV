<?php

namespace App\Model\Plannification;

/**
 * Java equivalent: ChairModel
 *
 * UI-level model representing a single chair inside a coworking espace.
 * The three states are preserved exactly:
 *   MINE      → reserved by the currently logged-in user
 *   AVAILABLE → free to reserve
 *   TAKEN     → reserved by a different user
 *
 * Used by CoworkingReservationController to build the seat-grid view
 * before passing it to Twig (replaces the JavaFX ObservableList<ChairModel>).
 */
final class ChairViewModel
{
    /**
     * Java: ChairModel.State enum — kept identical.
     */
    public const STATE_MINE      = 'MINE';
    public const STATE_AVAILABLE = 'AVAILABLE';
    public const STATE_TAKEN     = 'TAKEN';

    /** Java: seatNumber (final int) */
    private readonly int $seatNumber;

    /** Java: state (mutable) */
    private string $state;

    /** Java: reservedByUserId (nullable Integer) */
    private ?int $reservedByUserId;

    public function __construct(int $seatNumber, string $state, ?int $reservedByUserId = null)
    {
        $this->seatNumber       = $seatNumber;
        $this->state            = $state;
        $this->reservedByUserId = $reservedByUserId;
    }

    // ── Java API preserved ───────────────────────────────────────────────────

    public function getSeatNumber(): int { return $this->seatNumber; }

    public function getState(): string { return $this->state; }
    public function setState(string $state): void { $this->state = $state; }

    public function getReservedByUserId(): ?int { return $this->reservedByUserId; }
    public function setReservedByUserId(?int $id): void { $this->reservedByUserId = $id; }

    // ── Convenience helpers used by Twig ─────────────────────────────────────

    public function isMine(): bool      { return $this->state === self::STATE_MINE; }
    public function isAvailable(): bool { return $this->state === self::STATE_AVAILABLE; }
    public function isTaken(): bool     { return $this->state === self::STATE_TAKEN; }
}