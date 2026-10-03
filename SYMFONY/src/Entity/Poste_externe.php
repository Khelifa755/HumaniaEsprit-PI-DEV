<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'poste_externe')]
class Poste_externe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $titre;

    #[ORM\Column(type: 'text')]
    private string $description;

    #[ORM\Column(name: 'type_contrat', type: 'string', length: 50)]
    private string $typeContrat;

    #[ORM\Column(type: 'float')]
    private float $salaire;

    #[ORM\Column(name: 'competences_requises', type: 'text')]
    private string $competencesRequises;

    #[ORM\Column(name: 'experience_requise', type: 'string', length: 100)]
    private string $experienceRequise;

    #[ORM\Column(name: 'niveau_etude_requis', type: 'string', length: 50)]
    private string $niveauEtudeRequis;

    #[ORM\Column(type: 'string', length: 50)]
    private string $statut;

    #[ORM\Column(name: 'date_publication', type: 'date')]
    private \DateTimeInterface $datePublication;

    #[ORM\Column(name: 'date_cloture', type: 'date')]
    private \DateTimeInterface $dateCloture;

    #[ORM\Column(name: 'nombre_employe', type: 'integer')]
    private int $nombreEmploye;

    #[ORM\Column(type: 'string', length: 50)]
    private string $priorite;

    public function __construct()
    {
        $this->titre = '';
        $this->description = '';
        $this->typeContrat = 'CDI';
        $this->salaire = 0.0;
        $this->competencesRequises = '';
        $this->experienceRequise = '';
        $this->niveauEtudeRequis = '';
        $this->statut = 'Ouvert';
        $this->datePublication = new \DateTimeImmutable('today');
        $this->dateCloture = new \DateTimeImmutable('+1 month');
        $this->nombreEmploye = 1;
        $this->priorite = 'Moyenne';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getTypeContrat(): string
    {
        return $this->typeContrat;
    }

    public function setTypeContrat(string $typeContrat): static
    {
        $this->typeContrat = $typeContrat;

        return $this;
    }

    public function getSalaire(): float
    {
        return $this->salaire;
    }

    public function setSalaire(float $salaire): static
    {
        $this->salaire = $salaire;

        return $this;
    }

    public function getCompetencesRequises(): string
    {
        return $this->competencesRequises;
    }

    public function setCompetencesRequises(string $competencesRequises): static
    {
        $this->competencesRequises = $competencesRequises;

        return $this;
    }

    public function getExperienceRequise(): string
    {
        return $this->experienceRequise;
    }

    public function setExperienceRequise(string $experienceRequise): static
    {
        $this->experienceRequise = $experienceRequise;

        return $this;
    }

    public function getNiveauEtudeRequis(): string
    {
        return $this->niveauEtudeRequis;
    }

    public function setNiveauEtudeRequis(string $niveauEtudeRequis): static
    {
        $this->niveauEtudeRequis = $niveauEtudeRequis;

        return $this;
    }

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getDatePublication(): \DateTimeInterface
    {
        return $this->datePublication;
    }

    public function setDatePublication(\DateTimeInterface $datePublication): static
    {
        $this->datePublication = $datePublication;

        return $this;
    }

    public function getDateCloture(): \DateTimeInterface
    {
        return $this->dateCloture;
    }

    public function setDateCloture(\DateTimeInterface $dateCloture): static
    {
        $this->dateCloture = $dateCloture;

        return $this;
    }

    public function getNombreEmploye(): int
    {
        return $this->nombreEmploye;
    }

    public function setNombreEmploye(int $nombreEmploye): static
    {
        $this->nombreEmploye = $nombreEmploye;

        return $this;
    }

    public function getPriorite(): string
    {
        return $this->priorite;
    }

    public function setPriorite(string $priorite): static
    {
        $this->priorite = $priorite;

        return $this;
    }
}
