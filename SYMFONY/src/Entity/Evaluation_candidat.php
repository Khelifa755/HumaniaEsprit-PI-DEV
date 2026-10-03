<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\RECRUTEMENT\Evaluation_candidatRepository;

#[ORM\Entity(repositoryClass: Evaluation_candidatRepository::class)]
class Evaluation_candidat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "integer")]
    private int $candidature_externe_id;

    #[ORM\Column(type: "integer")]
    private int $entretien_id;

    #[ORM\Column(type: "integer")]
    private int $note_technique;

    #[ORM\Column(type: "integer")]
    private int $note_savoir_etre;

    #[ORM\Column(type: "integer")]
    private int $note_motivation;

    #[ORM\Column(type: "integer")]
    private int $note_culture_fit;

    #[ORM\Column(type: "text")]
    private string $commentaire;

    #[ORM\Column(type: "string", length: 50)]
    private string $recommandation;

    #[ORM\Column(type: "text")]
    private string $point_fort;

    #[ORM\Column(type: "text")]
    private string $axe_amelioration;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $date_evaluation;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCandidature_externe_id(): int
    {
        return $this->candidature_externe_id;
    }

    public function setCandidature_externe_id(int $value): self
    {
        $this->candidature_externe_id = $value;
        return $this;
    }

    public function getEntretien_id(): int
    {
        return $this->entretien_id;
    }

    public function setEntretien_id(int $value): self
    {
        $this->entretien_id = $value;
        return $this;
    }

    public function getNote_technique(): int
    {
        return $this->note_technique;
    }

    public function setNote_technique(int $value): self
    {
        $this->note_technique = $value;
        return $this;
    }

    public function getNote_savoir_etre(): int
    {
        return $this->note_savoir_etre;
    }

    public function setNote_savoir_etre(int $value): self
    {
        $this->note_savoir_etre = $value;
        return $this;
    }

    public function getNote_motivation(): int
    {
        return $this->note_motivation;
    }

    public function setNote_motivation(int $value): self
    {
        $this->note_motivation = $value;
        return $this;
    }

    public function getNote_culture_fit(): int
    {
        return $this->note_culture_fit;
    }

    public function setNote_culture_fit(int $value): self
    {
        $this->note_culture_fit = $value;
        return $this;
    }

    public function getCommentaire(): string
    {
        return $this->commentaire;
    }

    public function setCommentaire(string $value): self
    {
        $this->commentaire = $value;
        return $this;
    }

    public function getRecommandation(): string
    {
        return $this->recommandation;
    }

    public function setRecommandation(string $value): self
    {
        $this->recommandation = $value;
        return $this;
    }

    public function getPoint_fort(): string
    {
        return $this->point_fort;
    }

    public function setPoint_fort(string $value): self
    {
        $this->point_fort = $value;
        return $this;
    }

    public function getAxe_amelioration(): string
    {
        return $this->axe_amelioration;
    }

    public function setAxe_amelioration(string $value): self
    {
        $this->axe_amelioration = $value;
        return $this;
    }

    public function getDate_evaluation(): \DateTimeInterface
    {
        return $this->date_evaluation;
    }

    public function setDate_evaluation(\DateTimeInterface $value): self
    {
        $this->date_evaluation = $value;
        return $this;
    }
}