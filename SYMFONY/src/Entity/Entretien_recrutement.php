<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
class Entretien_recrutement
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "integer")]
    private int $candidature_externe_id;

    #[ORM\Column(type: "string", length: 50)]
    private string $type_entretien;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $date_entretien;

    #[ORM\Column(type: "string")]
    private string $heure_debut;

    #[ORM\Column(type: "string")]
    private string $heure_fin;

    #[ORM\Column(type: "text")]
    private string $intervieweurs_ids;

    #[ORM\Column(type: "string", length: 50)]
    private string $salle;

    #[ORM\Column(type: "string", length: 255)]
    private string $url_visio;

    #[ORM\Column(type: "string", length: 50)]
    private string $statut_entretien;

    #[ORM\Column(type: "float")]
    private float $note_entretien;

    #[ORM\Column(type: "integer")]
    private int $duree;

    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getCandidature_externe_id()
    {
        return $this->candidature_externe_id;
    }

    public function setCandidature_externe_id($value)
    {
        $this->candidature_externe_id = $value;
    }

    public function getType_entretien()
    {
        return $this->type_entretien;
    }

    public function setType_entretien($value)
    {
        $this->type_entretien = $value;
    }

    public function getDate_entretien()
    {
        return $this->date_entretien;
    }

    public function setDate_entretien($value)
    {
        $this->date_entretien = $value;
    }

    public function getHeure_debut()
    {
        return $this->heure_debut;
    }

    public function setHeure_debut($value)
    {
        $this->heure_debut = $value;
    }

    public function getHeure_fin()
    {
        return $this->heure_fin;
    }

    public function setHeure_fin($value)
    {
        $this->heure_fin = $value;
    }

    public function getIntervieweurs_ids()
    {
        return $this->intervieweurs_ids;
    }

    public function setIntervieweurs_ids($value)
    {
        $this->intervieweurs_ids = $value;
    }

    public function getSalle()
    {
        return $this->salle;
    }

    public function setSalle($value)
    {
        $this->salle = $value;
    }

    public function getUrl_visio()
    {
        return $this->url_visio;
    }

    public function setUrl_visio($value)
    {
        $this->url_visio = $value;
    }

    public function getStatut_entretien()
    {
        return $this->statut_entretien;
    }

    public function setStatut_entretien($value)
    {
        $this->statut_entretien = $value;
    }

    public function getNote_entretien()
    {
        return $this->note_entretien;
    }

    public function setNote_entretien($value)
    {
        $this->note_entretien = $value;
    }

    public function getDuree()
    {
        return $this->duree;
    }

    public function setDuree($value)
    {
        $this->duree = $value;
    }
}
