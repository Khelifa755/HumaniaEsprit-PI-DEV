<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Categorieformation;
use Doctrine\Common\Collections\Collection;
use App\Entity\Actionpdi;

#[ORM\Entity]
class Formation
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "string", length: 255)]
    private string $titre;

    #[ORM\Column(type: "integer")]
    private int $duree;

    #[ORM\Column(type: "float")]
    private float $cout;

    #[ORM\Column(type: "string", length: 100)]
    private string $statutFormation;

        #[ORM\ManyToOne(targetEntity: Categorieformation::class, inversedBy: "formations")]
    #[ORM\JoinColumn(name: 'categorie_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Categorieformation $categorie_id;

    #[ORM\Column(type: "string")]
    private string $typeFormation;

    #[ORM\Column(type: "text")]
    private string $description;

    #[ORM\Column(type: "integer")]
    private int $formateur_id;

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

    public function getDuree()
    {
        return $this->duree;
    }

    public function setDuree($value)
    {
        $this->duree = $value;
    }

    public function getCout()
    {
        return $this->cout;
    }

    public function setCout($value)
    {
        $this->cout = $value;
    }

    public function getStatutFormation()
    {
        return $this->statutFormation;
    }

    public function setStatutFormation($value)
    {
        $this->statutFormation = $value;
    }

    public function getCategorie_id()
    {
        return $this->categorie_id;
    }

    public function setCategorie_id($value)
    {
        $this->categorie_id = $value;
    }

    public function getTypeFormation()
    {
        return $this->typeFormation;
    }

    public function setTypeFormation($value)
    {
        $this->typeFormation = $value;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($value)
    {
        $this->description = $value;
    }

    public function getFormateur_id()
    {
        return $this->formateur_id;
    }

    public function setFormateur_id($value)
    {
        $this->formateur_id = $value;
    }

    #[ORM\OneToMany(mappedBy: "formation_id", targetEntity: Module::class)]
    private Collection $modules;

        public function getModules(): Collection
        {
            return $this->modules;
        }
    
        public function addModule(Module $module): self
        {
            if (!$this->modules->contains($module)) {
                $this->modules[] = $module;
                $module->setFormation_id($this);
            }
    
            return $this;
        }
    
        public function removeModule(Module $module): self
        {
            if ($this->modules->removeElement($module)) {
                // set the owning side to null (unless already changed)
                if ($module->getFormation_id() === $this) {
                    $module->setFormation_id(null);
                }
            }
    
            return $this;
        }

    #[ORM\OneToMany(mappedBy: "formation_id", targetEntity: Sessionformation::class)]
    private Collection $sessionformations;

    #[ORM\OneToMany(mappedBy: "formation_id", targetEntity: Actionpdi::class)]
    private Collection $actionpdis;
}
