<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Competence;

#[ORM\Entity]
class Competenceemploye
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "integer")]
    private int $niveauActuel;

    #[ORM\Column(type: "boolean")]
    private bool $niveauValide;

    #[ORM\Column(type: "string", length: 255)]
    private string $preuveUrl;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $dateEvaluation;

        #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: "competenceemployes")]
    #[ORM\JoinColumn(name: 'employe_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Utilisateur $employe_id;

        #[ORM\ManyToOne(targetEntity: Competence::class, inversedBy: "competenceemployes")]
    #[ORM\JoinColumn(name: 'competence_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Competence $competence_id;

    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getNiveauActuel()
    {
        return $this->niveauActuel;
    }

    public function setNiveauActuel($value)
    {
        $this->niveauActuel = $value;
    }

    public function getNiveauValide()
    {
        return $this->niveauValide;
    }

    public function setNiveauValide($value)
    {
        $this->niveauValide = $value;
    }

    public function getPreuveUrl()
    {
        return $this->preuveUrl;
    }

    public function setPreuveUrl($value)
    {
        $this->preuveUrl = $value;
    }

    public function getDateEvaluation()
    {
        return $this->dateEvaluation;
    }

    public function setDateEvaluation($value)
    {
        $this->dateEvaluation = $value;
    }

    public function getEmploye_id()
    {
        return $this->employe_id;
    }

    public function setEmploye_id($value)
    {
        $this->employe_id = $value;
    }

    public function getCompetence_id()
    {
        return $this->competence_id;
    }

    public function setCompetence_id($value)
    {
        $this->competence_id = $value;
    }
}
