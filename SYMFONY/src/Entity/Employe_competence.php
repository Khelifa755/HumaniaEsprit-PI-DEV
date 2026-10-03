<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\Collection;
use App\Entity\Sous_section_progression;

#[ORM\Entity]
class Employe_competence
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "string", length: 50)]
    private string $matricule;

    #[ORM\Column(type: "string", length: 255)]
    private string $posteActuel;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $dateEmbauche;

    #[ORM\Column(type: "string", length: 255)]
    private string $Departement;

    #[ORM\Column(type: "string", length: 50)]
    private string $nom;

    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getMatricule()
    {
        return $this->matricule;
    }

    public function setMatricule($value)
    {
        $this->matricule = $value;
    }

    public function getPosteActuel()
    {
        return $this->posteActuel;
    }

    public function setPosteActuel($value)
    {
        $this->posteActuel = $value;
    }

    public function getDateEmbauche()
    {
        return $this->dateEmbauche;
    }

    public function setDateEmbauche($value)
    {
        $this->dateEmbauche = $value;
    }

    public function getDepartement()
    {
        return $this->Departement;
    }

    public function setDepartement($value)
    {
        $this->Departement = $value;
    }

    public function getNom()
    {
        return $this->nom;
    }

    public function setNom($value)
    {
        $this->nom = $value;
    }

    #[ORM\OneToMany(mappedBy: "employe_id", targetEntity: Sous_section_progression::class)]
    private Collection $sous_section_progressions;

        public function getSous_section_progressions(): Collection
        {
            return $this->sous_section_progressions;
        }
    
        public function addSous_section_progression(Sous_section_progression $sous_section_progression): self
        {
            if (!$this->sous_section_progressions->contains($sous_section_progression)) {
                $this->sous_section_progressions[] = $sous_section_progression;
                $sous_section_progression->setEmploye_id($this);
            }
    
            return $this;
        }
    
        public function removeSous_section_progression(Sous_section_progression $sous_section_progression): self
        {
            if ($this->sous_section_progressions->removeElement($sous_section_progression)) {
                // set the owning side to null (unless already changed)
                if ($sous_section_progression->getEmploye_id() === $this) {
                    $sous_section_progression->setEmploye_id(null);
                }
            }
    
            return $this;
        }
}
