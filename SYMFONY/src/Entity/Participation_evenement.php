<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Repository\ParticipationEvenementRepository::class)]
class Participation_evenement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Evenement::class, inversedBy: 'participation_evenements')]
    #[ORM\JoinColumn(name: 'idEvenement', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Evenement $idEvenement = null;

    #[ORM\Column(name: 'idEmploye', type: 'integer')]
    private int $idEmploye;

    #[ORM\Column(name: 'dateParticipation', type: 'date')]
    private \DateTimeInterface $dateParticipation;

    #[ORM\Column(type: 'string', length: 50)]
    private string $statut;

    #[ORM\Column(name: 'creeLe', type: 'datetime')]
    private \DateTimeInterface $creeLe;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdEvenement(): ?Evenement
    {
        return $this->idEvenement;
    }

    public function setIdEvenement(?Evenement $value): self
    {
        $this->idEvenement = $value;

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

    public function getDateParticipation(): \DateTimeInterface
    {
        return $this->dateParticipation;
    }

    public function setDateParticipation(\DateTimeInterface $value): self
    {
        $this->dateParticipation = $value;

        return $this;
    }

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function setStatut(string $value): self
    {
        $this->statut = $value;

        return $this;
    }

    public function getCreeLe(): \DateTimeInterface
    {
        return $this->creeLe;
    }

    public function setCreeLe(\DateTimeInterface $value): self
    {
        $this->creeLe = $value;

        return $this;
    }
}
