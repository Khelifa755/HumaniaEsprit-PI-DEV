<?php

namespace App\Entity;

use App\Repository\PLANIFICATION\Coworking_reservationsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: Coworking_reservationsRepository::class)]
#[ORM\Table(name: 'coworking_reservations')]
#[ORM\UniqueConstraint(name: 'uniq_coworking_seat_day', columns: ['espace_id', 'chair_number', 'reservation_date'])]
#[ORM\UniqueConstraint(name: 'uniq_coworking_user_day', columns: ['espace_id', 'user_id', 'reservation_date'])]
class Coworking_reservations
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'espace_id', type: 'integer')]
    private int $espaceId;

    #[ORM\Column(name: 'chair_number', type: 'integer')]
    private int $chairNumber;

    #[ORM\Column(name: 'user_id', type: 'integer')]
    private int $userId;

    #[ORM\Column(name: 'reservation_date', type: 'date_immutable')]
    private \DateTimeImmutable $reservationDate;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEspaceId(): int
    {
        return $this->espaceId;
    }

    public function setEspaceId(int $espaceId): self
    {
        $this->espaceId = $espaceId;

        return $this;
    }

    public function getChairNumber(): int
    {
        return $this->chairNumber;
    }

    public function setChairNumber(int $chairNumber): self
    {
        $this->chairNumber = $chairNumber;

        return $this;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function getReservationDate(): \DateTimeImmutable
    {
        return $this->reservationDate;
    }

    public function setReservationDate(\DateTimeImmutable $reservationDate): self
    {
        $this->reservationDate = $reservationDate;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
