<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
class Reservation
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "integer")]
    private int $user_id;

    #[ORM\Column(type: "integer")]
    private int $chair_id;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $reservation_date;

    #[ORM\Column(type: "string", length: 20)]
    private string $status;

    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getUser_id()
    {
        return $this->user_id;
    }

    public function setUser_id($value)
    {
        $this->user_id = $value;
    }

    public function getChair_id()
    {
        return $this->chair_id;
    }

    public function setChair_id($value)
    {
        $this->chair_id = $value;
    }

    public function getReservation_date()
    {
        return $this->reservation_date;
    }

    public function setReservation_date($value)
    {
        $this->reservation_date = $value;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($value)
    {
        $this->status = $value;
    }
}
