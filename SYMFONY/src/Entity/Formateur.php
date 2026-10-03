<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
class Formateur
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $utilisateur_id;

    #[ORM\Column(type: "string", length: 100)]
    private string $specialite;

    public function getUtilisateur_id()
    {
        return $this->utilisateur_id;
    }

    public function setUtilisateur_id($value)
    {
        $this->utilisateur_id = $value;
    }

    public function getSpecialite()
    {
        return $this->specialite;
    }

    public function setSpecialite($value)
    {
        $this->specialite = $value;
    }
}
