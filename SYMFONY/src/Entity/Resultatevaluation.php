<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Utilisateur;

#[ORM\Entity]
class Resultatevaluation
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "float")]
    private float $note;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $datePassage;

    #[ORM\Column(type: "string", length: 100)]
    private string $statut;

    #[ORM\Column(type: "string", length: 255)]
    private string $commentaire;

        #[ORM\ManyToOne(targetEntity: Evaluationformation::class, inversedBy: "resultatevaluations")]
    #[ORM\JoinColumn(name: 'evaluation_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Evaluationformation $evaluation_id;

        #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: "resultatevaluations")]
    #[ORM\JoinColumn(name: 'employe_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Utilisateur $employe_id;

    #[ORM\Column(type: "integer")]
    private int $score_pct;

    #[ORM\Column(type: "integer")]
    private int $niveau_delta;

    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getNote()
    {
        return $this->note;
    }

    public function setNote($value)
    {
        $this->note = $value;
    }

    public function getDatePassage()
    {
        return $this->datePassage;
    }

    public function setDatePassage($value)
    {
        $this->datePassage = $value;
    }

    public function getStatut()
    {
        return $this->statut;
    }

    public function setStatut($value)
    {
        $this->statut = $value;
    }

    public function getCommentaire()
    {
        return $this->commentaire;
    }

    public function setCommentaire($value)
    {
        $this->commentaire = $value;
    }

    public function getEvaluation_id()
    {
        return $this->evaluation_id;
    }

    public function setEvaluation_id($value)
    {
        $this->evaluation_id = $value;
    }

    public function getEmploye_id()
    {
        return $this->employe_id;
    }

    public function setEmploye_id($value)
    {
        $this->employe_id = $value;
    }

    public function getScore_pct()
    {
        return $this->score_pct;
    }

    public function setScore_pct($value)
    {
        $this->score_pct = $value;
    }

    public function getNiveau_delta()
    {
        return $this->niveau_delta;
    }

    public function setNiveau_delta($value)
    {
        $this->niveau_delta = $value;
    }
}
