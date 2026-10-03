<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Reservation_espaces
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Espaces::class, inversedBy: 'reservation_espacess')]
    #[ORM\JoinColumn(name: 'idEspace', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Espaces $idEspace = null;

    #[ORM\Column(name: 'idEmploye', type: 'integer')]
    private int $idEmploye;

    #[ORM\Column(name: 'dateReservation', type: 'date')]
    private \DateTimeInterface $dateReservation;

    #[ORM\Column(name: 'dateHeureDebut', type: 'datetime_immutable')]
    private \DateTimeImmutable $dateHeureDebut;

    #[ORM\Column(name: 'dateHeureFin', type: 'datetime_immutable')]
    private \DateTimeImmutable $dateHeureFin;

    #[ORM\Column(type: "string", length: 255)]
    private string $objectif;

    #[ORM\Column(type: "boolean")]
    private bool $statut;

    #[ORM\Column(name: 'creeLe', type: 'datetime_immutable')]
    private \DateTimeImmutable $creeLe;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdEspace(): ?Espaces
    {
        return $this->idEspace;
    }

    public function setIdEspace(?Espaces $value): self
    {
        $this->idEspace = $value;

        return $this;
    }

    public function getIdEmploye(): int
    {
        return $this->idEmploye;
    }

    public function setIdEmploye(int $value): self
    {
        $this->idEmploye = $value;
        return $this;
    }

    public function getDateReservation(): \DateTimeInterface
    {
        return $this->dateReservation;
    }

    public function setDateReservation(\DateTimeInterface $value): self
    {
        $this->dateReservation = $value;
        return $this;
    }

    public function getDateHeureDebut(): \DateTimeImmutable
    {
        return $this->dateHeureDebut;
    }

    public function setDateHeureDebut(\DateTimeImmutable $value): self
    {
        $this->dateHeureDebut = $value;
        return $this;
    }

    public function getDateHeureFin(): \DateTimeImmutable
    {
        return $this->dateHeureFin;
    }

    public function setDateHeureFin(\DateTimeImmutable $value): self
    {
        $this->dateHeureFin = $value;
        return $this;
    }

    public function getObjectif(): string
    {
        return $this->objectif;
    }

    public function setObjectif(string $value): self
    {
        $this->objectif = $value;
        return $this;
    }

    public function getStatut(): bool
    {
        return $this->statut;
    }

    public function setStatut(bool $value): self
    {
        $this->statut = $value;
        return $this;
    }

    public function getCreeLe(): \DateTimeImmutable
    {
        return $this->creeLe;
    }

    public function setCreeLe(\DateTimeImmutable $value): self
    {
        $this->creeLe = $value;
        return $this;
    }
}
