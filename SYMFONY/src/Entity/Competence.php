<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Categoriecompetence;
use Doctrine\Common\Collections\Collection;
use App\Entity\Competenceemploye;

#[ORM\Entity]
class Competence
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "string", length: 255)]
    private string $libelle;

    #[ORM\Column(type: "integer", name: "niveauMax")]
    private int $niveauMax;

    #[ORM\Column(type: "string", length: 100, name: "typeCompetence")]
    private string $typeCompetence;


    #[ORM\ManyToOne(targetEntity: Categoriecompetence::class, inversedBy: "competences")]
    #[ORM\JoinColumn(name: 'categorie_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Categoriecompetence $categorie_id;

    #[ORM\Column(type: "string", length: 50, name: "statutCompetence")]
    private string $statutCompetence;

    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getLibelle()
    {
        return $this->libelle;
    }

    public function setLibelle($value)
    {
        $this->libelle = $value;
    }

    public function getNiveauMax()
    {
        return $this->niveauMax;
    }

    public function setNiveauMax($value)
    {
        $this->niveauMax = $value;
    }

    public function getTypeCompetence()
    {
        return $this->typeCompetence;
    }

    public function setTypeCompetence($value)
    {
        $this->typeCompetence = $value;
    }

    public function getCategorie_id()
    {
        return $this->categorie_id;
    }

    public function setCategorie_id($value)
    {
        $this->categorie_id = $value;
    }

    public function getStatutCompetence()
    {
        return $this->statutCompetence;
    }

    public function setStatutCompetence($value)
    {
        $this->statutCompetence = $value;
    }

    #[ORM\OneToMany(mappedBy: "competence_id", targetEntity: Competenceemploye::class)]
    private Collection $competenceemployes;

    public function getCompetenceemployes(): Collection
    {
        return $this->competenceemployes;
    }

    public function addCompetenceemploye(Competenceemploye $competenceemploye): self
    {
        if (!$this->competenceemployes->contains($competenceemploye)) {
            $this->competenceemployes[] = $competenceemploye;
            $competenceemploye->setCompetence_id($this);
        }

        return $this;
    }

    public function removeCompetenceemploye(Competenceemploye $competenceemploye): self
    {
        if ($this->competenceemployes->removeElement($competenceemploye)) {
            // set the owning side to null (unless already changed)
            if ($competenceemploye->getCompetence_id() === $this) {
                $competenceemploye->setCompetence_id(null);
            }
        }

        return $this;
    }
}
