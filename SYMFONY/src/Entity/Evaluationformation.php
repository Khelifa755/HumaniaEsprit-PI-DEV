<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Sessionformation;
use Doctrine\Common\Collections\Collection;
use App\Entity\Evaluation_question;

#[ORM\Entity]
class Evaluationformation
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "string", length: 255)]
    private string $titre;

    #[ORM\Column(type: "string", length: 100)]
    private string $type;

    #[ORM\Column(type: "integer")]
    private int $duree;

        #[ORM\ManyToOne(targetEntity: Sessionformation::class, inversedBy: "evaluationformations")]
    #[ORM\JoinColumn(name: 'session_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Sessionformation $session_id;

    #[ORM\Column(type: "integer")]
    private int $score_requis;

    #[ORM\Column(type: "integer")]
    private int $niveau_succes;

    #[ORM\Column(type: "integer")]
    private int $niveau_echec;

    #[ORM\Column(type: "integer")]
    private int $competence_id;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $dateDebut;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $dateFin;

    #[ORM\Column(type: "integer")]
    private int $niveau_requis;

    #[ORM\Column(type: "string", length: 10)]
    private string $difficulte;

    #[ORM\Column(type: "string", length: 10)]
    private string $statut_eval;

    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getTitre()
    {
        return $this->titre;
    }

    public function setTitre($value)
    {
        $this->titre = $value;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($value)
    {
        $this->type = $value;
    }

    public function getDuree()
    {
        return $this->duree;
    }

    public function setDuree($value)
    {
        $this->duree = $value;
    }

    public function getSession_id()
    {
        return $this->session_id;
    }

    public function setSession_id($value)
    {
        $this->session_id = $value;
    }

    public function getScore_requis()
    {
        return $this->score_requis;
    }

    public function setScore_requis($value)
    {
        $this->score_requis = $value;
    }

    public function getNiveau_succes()
    {
        return $this->niveau_succes;
    }

    public function setNiveau_succes($value)
    {
        $this->niveau_succes = $value;
    }

    public function getNiveau_echec()
    {
        return $this->niveau_echec;
    }

    public function setNiveau_echec($value)
    {
        $this->niveau_echec = $value;
    }

    public function getCompetence_id()
    {
        return $this->competence_id;
    }

    public function setCompetence_id($value)
    {
        $this->competence_id = $value;
    }

    public function getDateDebut()
    {
        return $this->dateDebut;
    }

    public function setDateDebut($value)
    {
        $this->dateDebut = $value;
    }

    public function getDateFin()
    {
        return $this->dateFin;
    }

    public function setDateFin($value)
    {
        $this->dateFin = $value;
    }

    public function getNiveau_requis()
    {
        return $this->niveau_requis;
    }

    public function setNiveau_requis($value)
    {
        $this->niveau_requis = $value;
    }

    public function getDifficulte()
    {
        return $this->difficulte;
    }

    public function setDifficulte($value)
    {
        $this->difficulte = $value;
    }

    public function getStatut_eval()
    {
        return $this->statut_eval;
    }

    public function setStatut_eval($value)
    {
        $this->statut_eval = $value;
    }

    #[ORM\OneToMany(mappedBy: "evaluation_id", targetEntity: Evaluation_question::class)]
    private Collection $evaluation_questions;

    #[ORM\OneToMany(mappedBy: "evaluation_id", targetEntity: Resultatevaluation::class)]
    private Collection $resultatevaluations;
}
