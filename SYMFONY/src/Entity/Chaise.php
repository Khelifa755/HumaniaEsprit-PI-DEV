<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
class Chaise
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "integer")]
    private int $espace_id;

    #[ORM\Column(type: "integer")]
    private int $chair_number;

    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getEspace_id()
    {
        return $this->espace_id;
    }

    public function setEspace_id($value)
    {
        $this->espace_id = $value;
    }

    public function getChair_number()
    {
        return $this->chair_number;
    }

    public function setChair_number($value)
    {
        $this->chair_number = $value;
    }
}
