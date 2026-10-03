<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
class Poll_vote
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "integer")]
    private int $pollId;

    #[ORM\Column(type: "integer")]
    private int $optionId;

    #[ORM\Column(type: "integer")]
    private int $userId;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $votedAt;

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

    public function getOptionId()
    {
        return $this->optionId;
    }

    public function setOptionId($value)
    {
        $this->optionId = $value;
    }

    public function getUserId()
    {
        return $this->userId;
    }

    public function setUserId($value)
    {
        $this->userId = $value;
    }

    public function getVotedAt()
    {
        return $this->votedAt;
    }

    public function setVotedAt($value)
    {
        $this->votedAt = $value;
    }
}
