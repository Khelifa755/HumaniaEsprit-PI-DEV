<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Formation;
use Doctrine\Common\Collections\Collection;
use App\Entity\Evaluationformation;

#[ORM\Entity]
class Sessionformation
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $dateDebut;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $dateFin;

    #[ORM\Column(type: "string", length: 255)]
    private string $lieu;

    #[ORM\Column(type: "string", length: 100)]
    private string $statut;

        #[ORM\ManyToOne(targetEntity: Formation::class, inversedBy: "sessionformations")]
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

    public function getDateDebut()
    {
        return $this->dateDebut;
    }

    public function setDateDebut($value)
    {
        $this->dateDebut = $value;
    }

    public function getDateFin()
    {
        return $this->dateFin;
    }

    public function setDateFin($value)
    {
        $this->dateFin = $value;
    }

    public function getLieu()
    {
        return $this->lieu;
    }

    public function setLieu($value)
    {
        $this->lieu = $value;
    }

    public function getStatut()
    {
        return $this->statut;
    }

    public function setStatut($value)
    {
        $this->statut = $value;
    }

    public function getFormation_id()
    {
        return $this->formation_id;
    }

    public function setFormation_id($value)
    {
        $this->formation_id = $value;
    }

    #[ORM\OneToMany(mappedBy: "session_id", targetEntity: Evaluationformation::class)]
    private Collection $evaluationformations;

    #[ORM\OneToMany(mappedBy: "session_id", targetEntity: Inscriptionformation::class)]
    private Collection $inscriptionformations;
}
