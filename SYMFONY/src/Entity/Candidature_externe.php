<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'candidature_externe')]
#[ORM\HasLifecycleCallbacks]
class Candidature_externe
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'poste_externe_id', type: 'integer')]
    private int $posteExterneId = 0;

    #[ORM\Column(type: 'string', length: 100)]
    private string $nom;

    #[ORM\Column(type: 'string', length: 100)]
    private string $prenom;

    // === NOUVEAUX CHAMPS ===
    #[ORM\Column(type: 'string', length: 150, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $telephone = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $ville = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $experience = null; // Années d'expérience

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $formation = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $linkedin = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $disponibilite = null;

    #[ORM\Column(name: 'pretention_salariale', type: 'integer', nullable: true)]
    private ?int $pretentionSalariale = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $consentement = null;

    // CV et Lettre de motivation (fichiers)
    #[ORM\Column(name: 'cv_url', type: 'string', length: 255, nullable: true)]
    private ?string $cvUrl = null;

    #[ORM\Column(name: 'lettre_motivation_url', type: 'string', length: 255, nullable: true)]
    private ?string $lettreMotivationUrl = null;   // Texte ou fichier

    #[ORM\Column(name: 'lettre_motivation_file', type: 'string', length: 255, nullable: true)]
    private ?string $lettreMotivationFile = null;  // Nouveau : fichier PDF

    #[ORM\Column(name: 'date_depot', type: 'date')]
    private \DateTimeInterface $dateDepot;

    #[ORM\Column(type: 'string', length: 50)]
    private string $statut;

    #[ORM\Column(name: 'etape_pipeline', type: 'string', length: 50)]
    private string $etapePipeline;

    #[ORM\Column(name: 'scoring_ia', type: 'float')]
    private float $scoringIa;

    #[ORM\Column(name: 'derniere_modification', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $derniereModification = null;

    public function __construct()
    {
        $this->nom = '';
        $this->prenom = '';
        $this->dateDepot = new \DateTime('today');
        $this->statut = 'En attente';
        $this->etapePipeline = 'Réception';
        $this->scoringIa = 0.0;
        $this->cvUrl = null;
        $this->lettreMotivationUrl = null;
        $this->lettreMotivationFile = null;
        $this->consentement = false;
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function touchDerniereModification(): void
    {
        $this->derniereModification = new \DateTime();
    }

    // Getters & Setters (je te donne seulement les nouveaux pour gagner du temps)
    public function getEmail(): ?string
    {
        return $this->email;
    }
    public function setEmail(?string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }
    public function setTelephone(?string $telephone): static
    {
        $this->telephone = $telephone;
        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }
    public function setVille(?string $ville): static
    {
        $this->ville = $ville;
        return $this;
    }

    public function getExperience(): ?string
    {
        return $this->experience;
    }
    public function setExperience(?string $experience): static
    {
        $this->experience = $experience;
        return $this;
    }

    public function getFormation(): ?string
    {
        return $this->formation;
    }
    public function setFormation(?string $formation): static
    {
        $this->formation = $formation;
        return $this;
    }

    public function getLinkedin(): ?string
    {
        return $this->linkedin;
    }
    public function setLinkedin(?string $linkedin): static
    {
        $this->linkedin = $linkedin;
        return $this;
    }

    public function getDisponibilite(): ?string
    {
        return $this->disponibilite;
    }
    public function setDisponibilite(?string $disponibilite): static
    {
        $this->disponibilite = $disponibilite;
        return $this;
    }

    public function getPretentionSalariale(): ?int
    {
        return $this->pretentionSalariale;
    }
    public function setPretentionSalariale(?int $pretentionSalariale): static
    {
        $this->pretentionSalariale = $pretentionSalariale;
        return $this;
    }

    public function isConsentement(): ?bool
    {
        return $this->consentement;
    }

    public function setConsentement(?bool $consentement): static
    {
        $this->consentement = $consentement;
        return $this;
    }

    public function getLettreMotivationFile(): ?string
    {
        return $this->lettreMotivationFile;
    }
    public function setLettreMotivationFile(?string $lettreMotivationFile): static
    {
        $this->lettreMotivationFile = $lettreMotivationFile;
        return $this;
    }

    public function getPosteExterneId(): int
    {
        return $this->posteExterneId;
    }
    public function setPosteExterneId(int $id): static
    {
        $this->posteExterneId = $id;
        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function getPrenom(): string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;
        return $this;
    }

    public function getDateDepot(): \DateTimeInterface
    {
        return $this->dateDepot;
    }

    public function setDateDepot(\DateTimeInterface $dateDepot): static
    {
        $this->dateDepot = $dateDepot;
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

    public function getEtapePipeline(): string
    {
        return $this->etapePipeline;
    }

    public function setEtapePipeline(string $etapePipeline): static
    {
        $this->etapePipeline = $etapePipeline;
        return $this;
    }

    public function getScoringIa(): float
    {
        return $this->scoringIa;
    }

    public function setScoringIa(float $scoringIa): static
    {
        $this->scoringIa = $scoringIa;
        return $this;
    }

    public function getCvUrl(): ?string
    {
        return $this->cvUrl;
    }

    public function setCvUrl(?string $cvUrl): static
    {
        $this->cvUrl = $cvUrl;
        return $this;
    }

    public function getLettreMotivationUrl(): ?string
    {
        return $this->lettreMotivationUrl;
    }

    public function setLettreMotivationUrl(?string $lettreMotivationUrl): static
    {
        $this->lettreMotivationUrl = $lettreMotivationUrl;
        return $this;
    }

    // Alias pour le controller
    public function getMotivation(): ?string
    {
        return $this->lettreMotivationUrl;
    }

    public function setMotivation(?string $motivation): static
    {
        $this->lettreMotivationUrl = $motivation;
        return $this;
    }

    public function getDerniereModification(): ?\DateTimeInterface
    {
        return $this->derniereModification;
    }

    public function setDerniereModification(?\DateTimeInterface $derniereModification): static
    {
        $this->derniereModification = $derniereModification;
        return $this;   
    
    }
}