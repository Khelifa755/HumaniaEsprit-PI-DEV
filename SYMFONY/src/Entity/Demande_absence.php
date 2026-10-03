<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
class Demande_absence
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $date_demande;

    #[ORM\Column(type: "text")]
    private string $motif;

    #[ORM\Column(type: "string", length: 50)]
    private string $statut;

    #[ORM\Column(type: "integer")]
    private int $absence_id;

    #[ORM\Column(type: "integer")]
    private int $utilisateur_id;

    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getDate_demande()
    {
        return $this->date_demande;
    }

    public function setDate_demande($value)
    {
        $this->date_demande = $value;
    }

    public function getMotif()
    {
        return $this->motif;
    }

    public function setMotif($value)
    {
        $this->motif = $value;
    }

    public function getStatut()
    {
        return $this->statut;
    }

    public function setStatut($value)
    {
        $this->statut = $value;
    }

    public function getAbsence_id()
    {
        return $this->absence_id;
    }

    public function setAbsence_id($value)
    {
        $this->absence_id = $value;
    }

    public function getUtilisateur_id()
    {
        return $this->utilisateur_id;
    }

    public function setUtilisateur_id($value)
    {
        $this->utilisateur_id = $value;
    }
}
