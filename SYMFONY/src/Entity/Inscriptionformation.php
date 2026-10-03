<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Utilisateur;

#[ORM\Entity]
class Inscriptionformation
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $dateInscription;

    #[ORM\Column(type: "string", length: 100)]
    private string $statut;

    #[ORM\Column(type: "integer")]
    private int $progression;

    #[ORM\Column(type: "float")]
    private float $noteFinale;

        #[ORM\ManyToOne(targetEntity: Sessionformation::class, inversedBy: "inscriptionformations")]
    #[ORM\JoinColumn(name: 'session_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Sessionformation $session_id;

        #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: "inscriptionformations")]
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

    public function getDateInscription()
    {
        return $this->dateInscription;
    }

    public function setDateInscription($value)
    {
        $this->dateInscription = $value;
    }

    public function getStatut()
    {
        return $this->statut;
    }

    public function setStatut($value)
    {
        $this->statut = $value;
    }

    public function getProgression()
    {
        return $this->progression;
    }

    public function setProgression($value)
    {
        $this->progression = $value;
    }

    public function getNoteFinale()
    {
        return $this->noteFinale;
    }

    public function setNoteFinale($value)
    {
        $this->noteFinale = $value;
    }

    public function getSession_id()
    {
        return $this->session_id;
    }

    public function setSession_id($value)
    {
        $this->session_id = $value;
    }

    public function getEmploye_id()
    {
        return $this->employe_id;
    }

    public function setEmploye_id($value)
    {
        $this->employe_id = $value;
    }
}
