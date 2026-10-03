<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'poste_interne')]
class Poste_interne
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'type_poste', type: 'string', length: 100)]
    private string $typePoste;

    #[ORM\Column(type: 'float')]
    private float $remuneration;

    #[ORM\Column(name: 'date_debut', type: 'date', nullable: true)]
    private ?\DateTimeInterface $dateDebut = null;

    #[ORM\Column(name: 'date_fin', type: 'date', nullable: true)]
    private ?\DateTimeInterface $dateFin = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTypePoste(): string
    {
        return $this->typePoste;
    }

    public function setTypePoste(string $value): static
    {
        $this->typePoste = $value;

        return $this;
    }

    public function getRemuneration(): float
    {
        return $this->remuneration;
    }

    public function setRemuneration(float $value): static
    {
        $this->remuneration = $value;

        return $this;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(?\DateTimeInterface $value): static
    {
        $this->dateDebut = $value;

        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(?\DateTimeInterface $value): static
    {
        $this->dateFin = $value;

        return $this;
    }
}
