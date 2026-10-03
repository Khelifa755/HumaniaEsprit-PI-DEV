<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Formation;
use Doctrine\Common\Collections\Collection;
use App\Entity\Exercice_soumission;

#[ORM\Entity]
class Module
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id;

        #[ORM\ManyToOne(targetEntity: Formation::class, inversedBy: "modules")]
    #[ORM\JoinColumn(name: 'formation_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Formation $formation_id;

    #[ORM\Column(type: "string", length: 255)]
    private string $titre;

    #[ORM\Column(type: "text")]
    private string $description;

    #[ORM\Column(type: "string")]
    private string $type_contenu;

    #[ORM\Column(type: "integer")]
    private int $duree_minutes;

    #[ORM\Column(type: "integer")]
    private int $ordre;

    #[ORM\Column(type: "text")]
    private string $contenu_texte;

    #[ORM\Column(type: "string", length: 500)]
    private string $url_ressource;

    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getFormation_id()
    {
        return $this->formation_id;
    }

    public function setFormation_id($value)
    {
        $this->formation_id = $value;
    }

    public function getTitre()
    {
        return $this->titre;
    }

    public function setTitre($value)
    {
        $this->titre = $value;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($value)
    {
        $this->description = $value;
    }

    public function getType_contenu()
    {
        return $this->type_contenu;
    }

    public function setType_contenu($value)
    {
        $this->type_contenu = $value;
    }

    public function getDuree_minutes()
    {
        return $this->duree_minutes;
    }

    public function setDuree_minutes($value)
    {
        $this->duree_minutes = $value;
    }

    public function getOrdre()
    {
        return $this->ordre;
    }

    public function setOrdre($value)
    {
        $this->ordre = $value;
    }

    public function getContenu_texte()
    {
        return $this->contenu_texte;
    }

    public function setContenu_texte($value)
    {
        $this->contenu_texte = $value;
    }

    public function getUrl_ressource()
    {
        return $this->url_ressource;
    }

    public function setUrl_ressource($value)
    {
        $this->url_ressource = $value;
    }

    #[ORM\OneToMany(mappedBy: "module_id", targetEntity: Module_highlights::class)]
    private Collection $module_highlightss;

        public function getModule_highlightss(): Collection
        {
            return $this->module_highlightss;
        }
    
        public function addModule_highlights(Module_highlights $module_highlights): self
        {
            if (!$this->module_highlightss->contains($module_highlights)) {
                $this->module_highlightss[] = $module_highlights;
                $module_highlights->setModule_id($this);
            }
    
            return $this;
        }
    
        public function removeModule_highlights(Module_highlights $module_highlights): self
        {
            if ($this->module_highlightss->removeElement($module_highlights)) {
                // set the owning side to null (unless already changed)
                if ($module_highlights->getModule_id() === $this) {
                    $module_highlights->setModule_id(null);
                }
            }
    
            return $this;
        }

    #[ORM\OneToMany(mappedBy: "module_id", targetEntity: Module_notes::class)]
    private Collection $module_notess;

    #[ORM\OneToMany(mappedBy: "module_id", targetEntity: Module_section::class)]
    private Collection $module_sections;

    #[ORM\OneToMany(mappedBy: "module_id", targetEntity: Quiz_question::class)]
    private Collection $quiz_questions;

    #[ORM\OneToMany(mappedBy: "module_id", targetEntity: Module_progression::class)]
    private Collection $module_progressions;

    #[ORM\OneToMany(mappedBy: "module_id", targetEntity: Exercice_soumission::class)]
    private Collection $exercice_soumissions;
}
