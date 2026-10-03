<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
class Pipeline_etape
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "integer")]
    private int $poste_externe_id;

    #[ORM\Column(type: "integer")]
    private int $ordre;

    #[ORM\Column(type: "string", length: 100)]
    private string $libelle;

    #[ORM\Column(type: "text")]
    private string $description_etape;

    #[ORM\Column(type: "integer")]
    private int $duree_moyenne;

    #[ORM\Column(type: "string", length: 100)]
    private string $action_automatique;

    #[ORM\Column(type: "boolean")]
    private bool $obligatoire;

    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getPoste_externe_id()
    {
        return $this->poste_externe_id;
    }

    public function setPoste_externe_id($value)
    {
        $this->poste_externe_id = $value;
    }

    public function getOrdre()
    {
        return $this->ordre;
    }

    public function setOrdre($value)
    {
        $this->ordre = $value;
    }

    public function getLibelle()
    {
        return $this->libelle;
    }

    public function setLibelle($value)
    {
        $this->libelle = $value;
    }

    public function getDescription_etape()
    {
        return $this->description_etape;
    }

    public function setDescription_etape($value)
    {
        $this->description_etape = $value;
    }

    public function getDuree_moyenne()
    {
        return $this->duree_moyenne;
    }

    public function setDuree_moyenne($value)
    {
        $this->duree_moyenne = $value;
    }

    public function getAction_automatique()
    {
        return $this->action_automatique;
    }

    public function setAction_automatique($value)
    {
        $this->action_automatique = $value;
    }

    public function getObligatoire()
    {
        return $this->obligatoire;
    }

    public function setObligatoire($value)
    {
        $this->obligatoire = $value;
    }
}
