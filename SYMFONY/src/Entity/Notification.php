<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Commentaire;

#[ORM\Entity]
class Notification
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "string", length: 100)]
    private string $titre;

    #[ORM\Column(type: "text")]
    private string $message;

    #[ORM\Column(type: "string", length: 50)]
    private string $type;

    #[ORM\Column(type: "datetime", name: "dateCreation")]
    private \DateTimeInterface $dateCreation;

        #[ORM\ManyToOne(targetEntity: Users::class, inversedBy: "notifications")]
    #[ORM\JoinColumn(name: 'userid', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Users $userId;

    #[ORM\Column(type: "boolean")]
    private bool $seen;

    #[ORM\Column(type: "datetime", nullable: true, name: "dateViewAt")]
    private ?\DateTimeInterface $dateViewAt = null;

    #[ORM\ManyToOne(targetEntity: Users::class, inversedBy: "notifications")]
    #[ORM\JoinColumn(name: 'relatedUserId', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: true)]
    private ?Users $relatedUserId = null;

    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getTitre()
    {
        return $this->titre;
    }

    public function setTitre($value)
    {
        $this->titre = $value;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage($value)
    {
        $this->message = $value;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($value)
    {
        $this->type = $value;
    }

    public function getDateCreation()
    {
        return $this->dateCreation;
    }

    public function setDateCreation($value)
    {
        $this->dateCreation = $value;
    }

    public function getUserId()
    {
        return $this->userId;
    }

    public function setUserId($value)
    {
        $this->userId = $value;
    }

    public function getSeen()
    {
        return $this->seen;
    }

    public function setSeen($value)
    {
        $this->seen = $value;
    }

    public function getDateViewAt()
    {
        return $this->dateViewAt;
    }

    public function setDateViewAt($value)
    {
        $this->dateViewAt = $value;
    }

    public function getRelatedUserId()
    {
        return $this->relatedUserId;
    }

    public function setRelatedUserId($value)
    {
        $this->relatedUserId = $value;
    }
}