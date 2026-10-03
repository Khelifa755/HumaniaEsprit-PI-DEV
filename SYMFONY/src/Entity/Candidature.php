<?php

namespace App\Entity;

use App\Repository\Utilisateur\CandidatureRepository;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: CandidatureRepository::class)]
class Candidature
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $poste_interne_id;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $candidat_id;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $employe_id;

    #[ORM\Column(type: "string", length: 50, nullable: true)]
    private ?string $type_candidat;

    #[ORM\Column(type: "date", nullable: true)]
    private ?\DateTimeInterface $date_depot;

    #[ORM\Column(type: "string", length: 50, nullable: true)]
    private ?string $statut;

    #[ORM\Column(type: "string", length: 50, nullable: true)]
    private ?string $etape_pipeline;

    #[ORM\Column(type: "float", nullable: true)]
    private ?float $scoring_ia;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $cv_url;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $lettre_motivation_url;

    #[ORM\Column(type: "date", nullable: true)]
    private ?\DateTimeInterface $derniere_modification;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $commentaires_rh;

    #[ORM\Column(type: "float", nullable: true)]
    private ?float $salaire_pretendu;

    #[ORM\Column(type: "string", length: 100, nullable: true)]
    private ?string $nom;

    #[ORM\Column(type: "string", length: 100, nullable: true)]
    private ?string $prenom;

    #[ORM\Column(type: "string", length: 100, nullable: true)]
    private ?string $email;

    // Runtime property (not in database) - tracks if already converted
    private bool $alreadyConverted = false;

    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getPoste_interne_id()
    {
        return $this->poste_interne_id;
    }

    public function setPoste_interne_id($value)
    {
        $this->poste_interne_id = $value;
    }

    public function getCandidat_id()
    {
        return $this->candidat_id;
    }

    public function setCandidat_id($value)
    {
        $this->candidat_id = $value;
    }

    public function getEmploye_id()
    {
        return $this->employe_id;
    }

    public function setEmploye_id($value)
    {
        $this->employe_id = $value;
    }

    public function getType_candidat()
    {
        return $this->type_candidat;
    }

    public function setType_candidat($value)
    {
        $this->type_candidat = $value;
    }

    public function getDate_depot()
    {
        return $this->date_depot;
    }

    public function setDate_depot($value)
    {
        $this->date_depot = $value;
    }

    public function getStatut()
    {
        return $this->statut;
    }

    public function setStatut($value)
    {
        $this->statut = $value;
    }

    public function getEtape_pipeline()
    {
        return $this->etape_pipeline;
    }

    public function setEtape_pipeline($value)
    {
        $this->etape_pipeline = $value;
    }

    public function getScoring_ia()
    {
        return $this->scoring_ia;
    }

    public function setScoring_ia($value)
    {
        $this->scoring_ia = $value;
    }

    public function getCv_url()
    {
        return $this->cv_url;
    }

    public function setCv_url($value)
    {
        $this->cv_url = $value;
    }

    public function getLettre_motivation_url()
    {
        return $this->lettre_motivation_url;
    }

    public function setLettre_motivation_url($value)
    {
        $this->lettre_motivation_url = $value;
    }

    public function getDerniere_modification()
    {
        return $this->derniere_modification;
    }

    public function setDerniere_modification($value)
    {
        $this->derniere_modification = $value;
    }

    public function getCommentaires_rh()
    {
        return $this->commentaires_rh;
    }

    public function setCommentaires_rh($value)
    {
        $this->commentaires_rh = $value;
    }

    public function getSalaire_pretendu()
    {
        return $this->salaire_pretendu;
    }

    public function setSalaire_pretendu($value)
    {
        $this->salaire_pretendu = $value;
    }

    public function getNom()
    {
        return $this->nom;
    }

    public function setNom($value)
    {
        $this->nom = $value;
    }

    public function getPrenom()
    {
        return $this->prenom;
    }

    public function setPrenom($value)
    {
        $this->prenom = $value;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($value)
    {
        $this->email = $value;
    }

    // ── Camel-case helpers (used by repository) ────────────────────────────

    public function setCandidatId($value): self
    {
        $this->candidat_id = $value;
        return $this;
    }

    public function getCandidatId()
    {
        return $this->candidat_id;
    }

    public function setEmployeId($value): self
    {
        $this->employe_id = $value;
        return $this;
    }

    public function getEmployeId()
    {
        return $this->employe_id;
    }

    public function setTypeCandidat($value): self
    {
        $this->type_candidat = $value;
        return $this;
    }

    public function getTypeCandidat()
    {
        return $this->type_candidat;
    }

    public function setDateDepot($value): self
    {
        $this->date_depot = $value;
        return $this;
    }

    public function getDateDepot()
    {
        return $this->date_depot;
    }

    public function setEtapePipeline($value): self
    {
        $this->etape_pipeline = $value;
        return $this;
    }

    public function getEtapePipeline()
    {
        return $this->etape_pipeline;
    }

    public function setScoringIa($value): self
    {
        $this->scoring_ia = $value;
        return $this;
    }

    public function getScoringIa()
    {
        return $this->scoring_ia;
    }

    public function setCvUrl($value): self
    {
        $this->cv_url = $value;
        return $this;
    }

    public function getCvUrl()
    {
        return $this->cv_url;
    }

    public function setLettreMotivationUrl($value): self
    {
        $this->lettre_motivation_url = $value;
        return $this;
    }

    public function getLettreMotivationUrl()
    {
        return $this->lettre_motivation_url;
    }

    public function setCommentairesRh($value): self
    {
        $this->commentaires_rh = $value;
        return $this;
    }

    public function getCommentairesRh()
    {
        return $this->commentaires_rh;
    }

    public function setSalairePretendu($value): self
    {
        $this->salaire_pretendu = $value;
        return $this;
    }

    public function getSalairePretendu()
    {
        return $this->salaire_pretendu;
    }

    // ── Conversion tracking (runtime, not in database) ────────────────────

    public function setAlreadyConverted(bool $value): self
    {
        $this->alreadyConverted = $value;
        return $this;
    }

    public function isAlreadyConverted(): bool
    {
        return $this->alreadyConverted;
    }

    public function getAlreadyConverted(): bool
    {
        return $this->alreadyConverted;
    }
}
