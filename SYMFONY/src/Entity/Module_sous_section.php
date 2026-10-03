<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Module_section;
use Doctrine\Common\Collections\Collection;
use App\Entity\Exercice_soumission;

#[ORM\Entity]
class Module_sous_section
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id;

        #[ORM\ManyToOne(targetEntity: Module_section::class, inversedBy: "module_sous_sections")]
    #[ORM\JoinColumn(name: 'section_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Module_section $section_id;

    #[ORM\Column(type: "string", length: 255)]
    private string $titre;

    #[ORM\Column(type: "string")]
    private string $type_contenu;

    #[ORM\Column(type: "text")]
    private string $contenu_texte;

    #[ORM\Column(type: "string", length: 500)]
    private string $url_ressource;

    #[ORM\Column(type: "integer")]
    private int $duree_minutes;

    #[ORM\Column(type: "integer")]
    private int $ordre;

    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getSection_id()
    {
        return $this->section_id;
    }

    public function setSection_id($value)
    {
        $this->section_id = $value;
    }

    public function getTitre()
    {
        return $this->titre;
    }

    public function setTitre($value)
    {
        $this->titre = $value;
    }

    public function getType_contenu()
    {
        return $this->type_contenu;
    }

    public function setType_contenu($value)
    {
        $this->type_contenu = $value;
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

    #[ORM\OneToMany(mappedBy: "sous_section_id", targetEntity: Sous_section_progression::class)]
    private Collection $sous_section_progressions;

        public function getSous_section_progressions(): Collection
        {
            return $this->sous_section_progressions;
        }
    
        public function addSous_section_progression(Sous_section_progression $sous_section_progression): self
        {
            if (!$this->sous_section_progressions->contains($sous_section_progression)) {
                $this->sous_section_progressions[] = $sous_section_progression;
                $sous_section_progression->setSous_section_id($this);
            }
    
            return $this;
        }
    
        public function removeSous_section_progression(Sous_section_progression $sous_section_progression): self
        {
            if ($this->sous_section_progressions->removeElement($sous_section_progression)) {
                // set the owning side to null (unless already changed)
                if ($sous_section_progression->getSous_section_id() === $this) {
                    $sous_section_progression->setSous_section_id(null);
                }
            }
    
            return $this;
        }

    #[ORM\OneToMany(mappedBy: "sous_section_id", targetEntity: Exercice_soumission::class)]
    private Collection $exercice_soumissions;
}
