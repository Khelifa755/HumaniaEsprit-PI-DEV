<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
class Poll_option
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "integer")]
    private int $pollId;

    #[ORM\Column(type: "string", length: 200)]
    private string $optionText;

    #[ORM\Column(type: "integer")]
    private int $voteCount;

    #[ORM\Column(type: "integer")]
    private int $optionOrder;

    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getPollId()
    {
        return $this->pollId;
    }

    public function setPollId($value)
    {
        $this->pollId = $value;
    }

    public function getOptionText()
    {
        return $this->optionText;
    }

    public function setOptionText($value)
    {
        $this->optionText = $value;
    }

    public function getVoteCount()
    {
        return $this->voteCount;
    }

    public function setVoteCount($value)
    {
        $this->voteCount = $value;
    }

    public function getOptionOrder()
    {
        return $this->optionOrder;
    }

    public function setOptionOrder($value)
    {
        $this->optionOrder = $value;
    }
}
