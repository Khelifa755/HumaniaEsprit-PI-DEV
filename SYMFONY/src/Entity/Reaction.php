<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Commentaire;

#[ORM\Entity]
#[ORM\Table(name: 'reaction')]
class Reaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "string")]
    private string $type = 'LIKE';

    #[ORM\ManyToOne(targetEntity: Users::class, inversedBy: "reactions")]
    #[ORM\JoinColumn(name: 'userId', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: true)]
    private ?Users $userId = null;

    #[ORM\ManyToOne(targetEntity: Publication::class, inversedBy: "reactions")]
    #[ORM\JoinColumn(name: 'publicationId', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: true)]
    private ?Publication $publicationId = null;

    #[ORM\ManyToOne(targetEntity: Commentaire::class, inversedBy: "reactions")]
    #[ORM\JoinColumn(name: 'commentaireId', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: true)]
    private ?Commentaire $commentaireId = null;

    #[ORM\Column(type: "datetime", name: "dateCreation")]
    private \DateTimeInterface $dateCreation;

    public function getId() { return $this->id; }
    public function setId($value) { $this->id = $value; }

    public function getType() { return $this->type; }
    public function setType($value) { $this->type = $value; }

    public function getUserId() { return $this->userId; }
    public function setUserId($value) { $this->userId = $value; }

    public function getPublicationId() { return $this->publicationId; }
    public function setPublicationId($value) { $this->publicationId = $value; }

    public function getCommentaireId() { return $this->commentaireId; }
    public function setCommentaireId($value) { $this->commentaireId = $value; }

    public function getDateCreation() { return $this->dateCreation; }
    public function setDateCreation($value) { $this->dateCreation = $value; }
}
