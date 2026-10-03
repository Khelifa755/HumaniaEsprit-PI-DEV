<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'mention')]
class Mention
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Users::class)]
    #[ORM\JoinColumn(name: 'mentionedUserId', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Users $mentionedUserId;

    #[ORM\ManyToOne(targetEntity: Users::class)]
    #[ORM\JoinColumn(name: 'authorId', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Users $authorId;

    #[ORM\ManyToOne(targetEntity: Publication::class, inversedBy: "mentions")]
    #[ORM\JoinColumn(name: 'publicationId', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: true)]
    private ?Publication $publicationId = null;

    #[ORM\ManyToOne(targetEntity: Commentaire::class)]
    #[ORM\JoinColumn(name: 'commentaireId', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: true)]
    private ?Commentaire $commentaireId = null;

    #[ORM\Column(type: "datetime")]
    private \DateTime $createdAt;

    #[ORM\Column(type: "string", length: 50, nullable: true)]
    private ?string $status = 'UNREAD'; // UNREAD, READ

    // ── Getters & Setters ──

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getMentionedUserId(): Users
    {
        return $this->mentionedUserId;
    }

    public function setMentionedUserId(Users $mentionedUserId): self
    {
        $this->mentionedUserId = $mentionedUserId;
        return $this;
    }

    public function getAuthorId(): Users
    {
        return $this->authorId;
    }

    public function setAuthorId(Users $authorId): self
    {
        $this->authorId = $authorId;
        return $this;
    }

    public function getPublicationId(): ?Publication
    {
        return $this->publicationId;
    }

    public function setPublicationId(?Publication $publicationId): self
    {
        $this->publicationId = $publicationId;
        return $this;
    }

    public function getCommentaireId(): ?Commentaire
    {
        return $this->commentaireId;
    }

    public function setCommentaireId(?Commentaire $commentaireId): self
    {
        $this->commentaireId = $commentaireId;
        return $this;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;
        return $this;
    }
}
