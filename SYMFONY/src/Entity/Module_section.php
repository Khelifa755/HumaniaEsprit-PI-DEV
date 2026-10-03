<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Module;
use Doctrine\Common\Collections\Collection;
use App\Entity\Module_sous_section;

#[ORM\Entity]
class Module_section
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id;

        #[ORM\ManyToOne(targetEntity: Module::class, inversedBy: "module_sections")]
    #[ORM\JoinColumn(name: 'module_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Module $module_id;

    #[ORM\Column(type: "string", length: 255)]
    private string $titre;

    #[ORM\Column(type: "text")]
    private string $description;

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

    public function getModule_id()
    {
        return $this->module_id;
    }

    public function setModule_id($value)
    {
        $this->module_id = $value;
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

    public function getOrdre()
    {
        return $this->ordre;
    }

    public function setOrdre($value)
    {
        $this->ordre = $value;
    }

    #[ORM\OneToMany(mappedBy: "section_id", targetEntity: Module_sous_section::class)]
    private Collection $module_sous_sections;

        public function getModule_sous_sections(): Collection
        {
            return $this->module_sous_sections;
        }
    
        public function addModule_sous_section(Module_sous_section $module_sous_section): self
        {
            if (!$this->module_sous_sections->contains($module_sous_section)) {
                $this->module_sous_sections[] = $module_sous_section;
                $module_sous_section->setSection_id($this);
            }
    
            return $this;
        }
    
        public function removeModule_sous_section(Module_sous_section $module_sous_section): self
        {
            if ($this->module_sous_sections->removeElement($module_sous_section)) {
                // set the owning side to null (unless already changed)
                if ($module_sous_section->getSection_id() === $this) {
                    $module_sous_section->setSection_id(null);
                }
            }
    
            return $this;
        }
}
