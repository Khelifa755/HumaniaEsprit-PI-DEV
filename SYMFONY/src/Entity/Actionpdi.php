<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Formation;

#[ORM\Entity]
class Actionpdi
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "string", length: 100)]
    private string $typeAction;

    #[ORM\Column(type: "string", length: 100)]
    private string $statut;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $dateDebut;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $dateFinPrevue;

    #[ORM\Column(type: "integer")]
    private int $priorite;

        #[ORM\ManyToOne(targetEntity: Pdi::class, inversedBy: "actionpdis")]
    #[ORM\JoinColumn(name: 'pdi_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Pdi $pdi_id;

        #[ORM\ManyToOne(targetEntity: Formation::class, inversedBy: "actionpdis")]
    #[ORM\JoinColumn(name: 'formation_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Formation $formation_id;

    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getTypeAction()
    {
        return $this->typeAction;
    }

    public function setTypeAction($value)
    {
        $this->typeAction = $value;
    }

    public function getStatut()
    {
        return $this->statut;
    }

    public function setStatut($value)
    {
        $this->statut = $value;
    }

    public function getDateDebut()
    {
        return $this->dateDebut;
    }

    public function setDateDebut($value)
    {
        $this->dateDebut = $value;
    }

    public function getDateFinPrevue()
    {
        return $this->dateFinPrevue;
    }

    public function setDateFinPrevue($value)
    {
        $this->dateFinPrevue = $value;
    }

    public function getPriorite()
    {
        return $this->priorite;
    }

    public function setPriorite($value)
    {
        $this->priorite = $value;
    }

    public function getPdi_id()
    {
        return $this->pdi_id;
    }

    public function setPdi_id($value)
    {
        $this->pdi_id = $value;
    }

    public function getFormation_id()
    {
        return $this->formation_id;
    }

    public function setFormation_id($value)
    {
        $this->formation_id = $value;
    }
}
