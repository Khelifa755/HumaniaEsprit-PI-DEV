<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
class Competence_niveau_history
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "integer")]
    private int $employe_id;

    #[ORM\Column(type: "integer")]
    private int $competence_id;

    #[ORM\Column(type: "integer")]
    private int $niveau;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $date_snapshot;

    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getEmploye_id()
    {
        return $this->employe_id;
    }

    public function setEmploye_id($value)
    {
        $this->employe_id = $value;
    }

    public function getCompetence_id()
    {
        return $this->competence_id;
    }

    public function setCompetence_id($value)
    {
        $this->competence_id = $value;
    }

    public function getNiveau()
    {
        return $this->niveau;
    }

    public function setNiveau($value)
    {
        $this->niveau = $value;
    }

    public function getDate_snapshot()
    {
        return $this->date_snapshot;
    }

    public function setDate_snapshot($value)
    {
        $this->date_snapshot = $value;
    }
}
