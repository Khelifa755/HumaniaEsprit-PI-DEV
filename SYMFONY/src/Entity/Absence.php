<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Absence
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "date")]
    private ?\DateTimeInterface $date_debut = null;

    #[ORM\Column(type: "date", nullable: true)]
    private ?\DateTimeInterface $date_fin = null;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $nbr_jours = null;

    #[ORM\Column(type: "string", length: 50)]
    private ?string $statut = null;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $type_absence_id = null;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $utilisateur_id = null;

    #[ORM\Column(type: "string", length: 5, nullable: true)]
    private ?string $heure_debut = null;

    #[ORM\Column(type: "string", length: 5, nullable: true)]
    private ?string $heure_fin = null;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $duree_minutes = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $motif = null;

    public function getId(): ?int { return $this->id; }

    public function getDateDebut(): ?\DateTimeInterface { return $this->date_debut; }
    public function setDateDebut(?\DateTimeInterface $date_debut): static { $this->date_debut = $date_debut; return $this; }

    public function getDateFin(): ?\DateTimeInterface { return $this->date_fin; }
    public function setDateFin(?\DateTimeInterface $date_fin): static { $this->date_fin = $date_fin; return $this; }

    public function getNbrJours(): ?int { return $this->nbr_jours; }
    public function setNbrJours(?int $nbr_jours): static { $this->nbr_jours = $nbr_jours; return $this; }

    public function getStatut(): ?string { return $this->statut; }
    public function setStatut(?string $statut): static { $this->statut = $statut; return $this; }

    public function getTypeAbsenceId(): ?int { return $this->type_absence_id; }
    public function setTypeAbsenceId(?int $type_absence_id): static { $this->type_absence_id = $type_absence_id; return $this; }

    public function getUtilisateurId(): ?int { return $this->utilisateur_id; }
    public function setUtilisateurId(?int $utilisateur_id): static { $this->utilisateur_id = $utilisateur_id; return $this; }

    public function getHeureDebut(): ?string { return $this->heure_debut; }
    public function setHeureDebut(?string $heure_debut): static { $this->heure_debut = $heure_debut; return $this; }

    public function getHeureFin(): ?string { return $this->heure_fin; }
    public function setHeureFin(?string $heure_fin): static { $this->heure_fin = $heure_fin; return $this; }

    public function getDureeMinutes(): ?int { return $this->duree_minutes; }
    public function setDureeMinutes(?int $duree_minutes): static { $this->duree_minutes = $duree_minutes; return $this; }

    public function getMotif(): ?string { return $this->motif; }
    public function setMotif(?string $motif): static { $this->motif = $motif; return $this; }
}