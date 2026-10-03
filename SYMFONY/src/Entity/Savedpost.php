<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Publication;

#[ORM\Entity]
#[ORM\Table(name: 'savedpost')]
class Savedpost
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Users::class, inversedBy: "savedposts")]
    #[ORM\JoinColumn(name: 'userId', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: true)]
    private ?Users $userId = null;

    #[ORM\ManyToOne(targetEntity: Publication::class, inversedBy: "savedposts")]
    #[ORM\JoinColumn(name: 'publicationId', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Publication $publicationId;

    #[ORM\Column(type: "datetime", name: "savedAt")]
    private \DateTimeInterface $savedAt;

    public function getId() { return $this->id; }
    public function setId($value) { $this->id = $value; }

    public function getUserId() { return $this->userId; }
    public function setUserId($value) { $this->userId = $value; }

    public function getPublicationId() { return $this->publicationId; }
    public function setPublicationId($value) { $this->publicationId = $value; }

    public function getSavedAt() { return $this->savedAt; }
    public function setSavedAt($value) { $this->savedAt = $value; }
}
