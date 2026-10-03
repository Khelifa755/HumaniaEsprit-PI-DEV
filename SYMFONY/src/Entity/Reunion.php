<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Reunion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 255)]
    private string $titre;

    #[ORM\Column(type: "text")]
    private string $description;

    #[ORM\Column(name: 'dateHeureDebut', type: 'datetime_immutable')]
    private \DateTimeInterface $dateHeureDebut;

    #[ORM\Column(name: 'dateHeureFin', type: 'datetime_immutable')]
    private \DateTimeInterface $dateHeureFin;

    #[ORM\ManyToOne(targetEntity: Espaces::class, inversedBy: 'reunions')]
    #[ORM\JoinColumn(name: 'idSalle', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: true)]
    private ?Espaces $idSalle = null;

    #[ORM\Column(name: 'nomOrganisateur', type: 'string', length: 255)]
    private string $nomOrganisateur;

    #[ORM\Column(name: 'emailOrganisateur', type: 'string', length: 255)]
    private string $emailOrganisateur;

    #[ORM\Column(type: "text")]
    private string $participants;

    #[ORM\Column(type: "boolean")]
    private bool $statut;

    #[ORM\Column(name: 'enLigne', type: 'boolean')]
    private bool $enLigne;

    #[ORM\Column(name: 'creeLe', type: 'datetime_immutable')]
    private \DateTimeInterface $creeLe;

    #[ORM\Column(type: "bigint", nullable: true)]
    private ?string $zoom_meeting_id = '0';

    #[ORM\Column(type: "string", length: 512, nullable: true)]
    private ?string $zoom_join_url = '';

    #[ORM\Column(type: "string", length: 512, nullable: true)]
    private ?string $zoom_start_url = '';

    #[ORM\Column(type: "string", length: 64, nullable: true)]
    private ?string $zoom_password = '';

    // =========================================================================
    // Getters / Setters
    // =========================================================================

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): string
    {
        return $this->titre;
    }

    public function setTitre($value): void
    {
        $this->titre = $value;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription($value): void
    {
        $this->description = $value;
    }

    public function getDateHeureDebut(): \DateTimeInterface
    {
        return $this->dateHeureDebut;
    }

    public function setDateHeureDebut($value): void
    {
        $this->dateHeureDebut = $value;
    }

    public function getDateHeureFin(): \DateTimeInterface
    {
        return $this->dateHeureFin;
    }

    public function setDateHeureFin($value): void
    {
        $this->dateHeureFin = $value;
    }

    public function getIdSalle(): ?Espaces
    {
        return $this->idSalle;
    }

    public function setIdSalle(?Espaces $value): self
    {
        $this->idSalle = $value;
        return $this;
    }

    public function getNomOrganisateur(): string
    {
        return $this->nomOrganisateur;
    }

    public function setNomOrganisateur($value): void
    {
        $this->nomOrganisateur = $value;
    }

    public function getEmailOrganisateur(): string
    {
        return $this->emailOrganisateur;
    }

    public function setEmailOrganisateur($value): void
    {
        $this->emailOrganisateur = $value;
    }

    public function getParticipants(): string
    {
        return $this->participants;
    }

    public function setParticipants($value): void
    {
        $this->participants = $value;
    }

    public function getStatut(): bool
    {
        return $this->statut;
    }

    public function setStatut($value): void
    {
        $this->statut = $value;
    }

    public function getEnLigne(): bool
    {
        return $this->enLigne;
    }

    public function setEnLigne($value): void
    {
        $this->enLigne = $value;
    }

    public function getCreeLe(): \DateTimeInterface
    {
        return $this->creeLe;
    }

    public function setCreeLe($value): void
    {
        $this->creeLe = $value;
    }

    // ── Zoom ─────────────────────────────────────────────────────────────────
    // Getters avec underscore (utilisés dans le contrôleur PHP)
    // + alias camelCase (utilisés dans les templates Twig)
    // ─────────────────────────────────────────────────────────────────────────

    public function getZoom_meeting_id(): ?string
    {
        return $this->zoom_meeting_id;
    }

    public function setZoom_meeting_id(?string $value): void
    {
        $this->zoom_meeting_id = $value;
    }

    /** Alias camelCase pour Twig : {{ reunion.zoomMeetingId }} */
    public function getZoomMeetingId(): ?string
    {
        return $this->zoom_meeting_id;
    }

    // ─────────────────────────────────────────────────────────────────────────

    public function getZoom_join_url(): ?string
    {
        return $this->zoom_join_url;
    }

    public function setZoom_join_url(?string $value): void
    {
        $this->zoom_join_url = $value;
    }

    /** Alias camelCase pour Twig : {{ reunion.zoomJoinUrl }} */
    public function getZoomJoinUrl(): ?string
    {
        return $this->zoom_join_url;
    }

    // ─────────────────────────────────────────────────────────────────────────

    public function getZoom_start_url(): ?string
    {
        return $this->zoom_start_url;
    }

    public function setZoom_start_url(?string $value): void
    {
        $this->zoom_start_url = $value;
    }

    /** Alias camelCase pour Twig : {{ reunion.zoomStartUrl }} */
    public function getZoomStartUrl(): ?string
    {
        return $this->zoom_start_url;
    }

    // ─────────────────────────────────────────────────────────────────────────

    public function getZoom_password(): ?string
    {
        return $this->zoom_password;
    }

    public function setZoom_password(?string $value): void
    {
        $this->zoom_password = $value;
    }

    /** Alias camelCase pour Twig : {{ reunion.zoomPassword }} */
    public function getZoomPassword(): ?string
    {
        return $this->zoom_password;
    }
}