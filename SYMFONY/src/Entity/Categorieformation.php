<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\Collection;
use App\Entity\Formation;

#[ORM\Entity]
class Categorieformation
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "string", length: 255)]
    private string $libelle;

    #[ORM\Column(type: "string", length: 50)]
    private string $couleur;

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

    public function getCouleur()
    {
        return $this->couleur;
    }

    public function setCouleur($value)
    {
        $this->couleur = $value;
    }

    #[ORM\OneToMany(mappedBy: "categorie_id", targetEntity: Formation::class)]
    private Collection $formations;

        public function getFormations(): Collection
        {
            return $this->formations;
        }
    
        public function addFormation(Formation $formation): self
        {
            if (!$this->formations->contains($formation)) {
                $this->formations[] = $formation;
                $formation->setCategorie_id($this);
            }
    
            return $this;
        }
    
        public function removeFormation(Formation $formation): self
        {
            if ($this->formations->removeElement($formation)) {
                // set the owning side to null (unless already changed)
                if ($formation->getCategorie_id() === $this) {
                    $formation->setCategorie_id(null);
                }
            }
    
            return $this;
        }
}
