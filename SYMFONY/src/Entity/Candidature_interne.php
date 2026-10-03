<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'candidature_interne')]
#[ORM\HasLifecycleCallbacks]
class Candidature_interne
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'poste_actuel', type: 'string', length: 150)]
    private string $posteActuel = '';

    #[ORM\Column(name: 'nouveau_poste', type: 'string', length: 150)]
    private string $nouveauPoste = '';

    #[ORM\Column(name: 'nouveau_salaire', type: 'float')]
    private float $nouveauSalaire = 0.0;

    #[ORM\Column(name: 'date_demande', type: 'date_immutable', nullable: true)]
    private ?\DateTimeImmutable $dateDemande = null;

    #[ORM\Column(type: 'text')]
    private string $motif = '';

    #[ORM\Column(name: 'derniere_modification', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $derniereModification = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $statut = null;

    public function __construct()
    {
        $this->dateDemande = new \DateTimeImmutable('today');
        $this->statut = 'En attente';
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function touchDerniereModification(): void
    {
        $this->derniereModification = new \DateTimeImmutable();
        if ($this->statut === null || $this->statut === '') {
            $this->statut = 'En attente';
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPosteActuel(): string
    {
        return $this->posteActuel;
    }

    public function setPosteActuel(string $posteActuel): static
    {
        $this->posteActuel = $posteActuel;
        return $this;
    }

    public function getNouveauPoste(): string
    {
        return $this->nouveauPoste;
    }

    public function setNouveauPoste(string $nouveauPoste): static
    {
        $this->nouveauPoste = $nouveauPoste;
        return $this;
    }

    public function getNouveauSalaire(): float
    {
        return $this->nouveauSalaire;
    }

    public function setNouveauSalaire(float $nouveauSalaire): static
    {
        $this->nouveauSalaire = $nouveauSalaire;
        return $this;
    }

    public function getDateDemande(): ?\DateTimeImmutable
    {
        return $this->dateDemande;
    }

    public function setDateDemande(\DateTimeInterface $dateDemande): static
    {
        if ($dateDemande instanceof \DateTimeImmutable) {
            $this->dateDemande = $dateDemande;
        } else {
            $this->dateDemande = \DateTimeImmutable::createFromMutable($dateDemande);
        }
        return $this;
    }

    public function getMotif(): string
    {
        return $this->motif;
    }

    public function setMotif(string $motif): static
    {
        $this->motif = $motif;
        return $this;
    }

    public function getDerniereModification(): ?\DateTimeImmutable
    {
        return $this->derniereModification;
    }

    public function setDerniereModification(?\DateTimeInterface $derniereModification): static
    {
        if ($derniereModification === null) {
            $this->derniereModification = null;
        } elseif ($derniereModification instanceof \DateTimeImmutable) {
            $this->derniereModification = $derniereModification;
        } else {
            $this->derniereModification = \DateTimeImmutable::createFromMutable($derniereModification);
        }
        return $this;
    }

    public function getStatut(): string
    {
        return $this->statut ?? 'En attente';
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }
}
