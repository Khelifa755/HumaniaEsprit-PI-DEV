<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Publication;

#[ORM\Entity]
class Poll
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id;

        #[ORM\ManyToOne(targetEntity: Publication::class, inversedBy: "polls")]
    #[ORM\JoinColumn(name: 'publicationId', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Publication $publicationId;

    #[ORM\Column(type: "string", length: 500)]
    private string $question;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $closedAt;

    #[ORM\Column(type: "integer")]
    private int $totalVotes;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $expiresAt;

    #[ORM\Column(type: "boolean")]
    private bool $isAnonymous;

    #[ORM\Column(type: "boolean")]
    private bool $allowMultiple;

    #[ORM\Column(type: "integer")]
    private int $createdById;

    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getPublicationId()
    {
        return $this->publicationId;
    }

    public function setPublicationId($value)
    {
        $this->publicationId = $value;
    }

    public function getQuestion()
    {
        return $this->question;
    }

    public function setQuestion($value)
    {
        $this->question = $value;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setCreatedAt($value)
    {
        $this->createdAt = $value;
    }

    public function getClosedAt()
    {
        return $this->closedAt;
    }

    public function setClosedAt($value)
    {
        $this->closedAt = $value;
    }

    public function getTotalVotes()
    {
        return $this->totalVotes;
    }

    public function setTotalVotes($value)
    {
        $this->totalVotes = $value;
    }

    public function getExpiresAt()
    {
        return $this->expiresAt;
    }

    public function setExpiresAt($value)
    {
        $this->expiresAt = $value;
    }

    public function getIsAnonymous()
    {
        return $this->isAnonymous;
    }

    public function setIsAnonymous($value)
    {
        $this->isAnonymous = $value;
    }

    public function getAllowMultiple()
    {
        return $this->allowMultiple;
    }

    public function setAllowMultiple($value)
    {
        $this->allowMultiple = $value;
    }

    public function getCreatedById()
    {
        return $this->createdById;
    }

    public function setCreatedById($value)
    {
        $this->createdById = $value;
    }
}
