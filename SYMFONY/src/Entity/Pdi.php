<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Utilisateur;
use Doctrine\Common\Collections\Collection;
use App\Entity\Actionpdi;

#[ORM\Entity]
class Pdi
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "integer")]
    private int $annee;

    #[ORM\Column(type: "integer")]
    private int $progressionGlobale;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $dateCreation;

    #[ORM\Column(type: "string", length: 100)]
    private string $statut;

        #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: "pdis")]
    #[ORM\JoinColumn(name: 'employe_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Utilisateur $employe_id;

    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getAnnee()
    {
        return $this->annee;
    }

    public function setAnnee($value)
    {
        $this->annee = $value;
    }

    public function getProgressionGlobale()
    {
        return $this->progressionGlobale;
    }

    public function setProgressionGlobale($value)
    {
        $this->progressionGlobale = $value;
    }

    public function getDateCreation()
    {
        return $this->dateCreation;
    }

    public function setDateCreation($value)
    {
        $this->dateCreation = $value;
    }

    public function getStatut()
    {
        return $this->statut;
    }

    public function setStatut($value)
    {
        $this->statut = $value;
    }

    public function getEmploye_id()
    {
        return $this->employe_id;
    }

    public function setEmploye_id($value)
    {
        $this->employe_id = $value;
    }

    #[ORM\OneToMany(mappedBy: "pdi_id", targetEntity: Actionpdi::class)]
    private Collection $actionpdis;

        public function getActionpdis(): Collection
        {
            return $this->actionpdis;
        }
    
        public function addActionpdi(Actionpdi $actionpdi): self
        {
            if (!$this->actionpdis->contains($actionpdi)) {
                $this->actionpdis[] = $actionpdi;
                $actionpdi->setPdi_id($this);
            }
    
            return $this;
        }
    
        public function removeActionpdi(Actionpdi $actionpdi): self
        {
            if ($this->actionpdis->removeElement($actionpdi)) {
                // set the owning side to null (unless already changed)
                if ($actionpdi->getPdi_id() === $this) {
                    $actionpdi->setPdi_id(null);
                }
            }
    
            return $this;
        }
}
