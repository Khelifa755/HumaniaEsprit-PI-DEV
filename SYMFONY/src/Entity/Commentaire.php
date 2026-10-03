<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Users;
use Doctrine\Common\Collections\Collection;
use App\Entity\Notification;

#[ORM\Entity]
#[ORM\Table(name: 'commentaire')]
class Commentaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "text")]
    private string $contenu;

    #[ORM\ManyToOne(targetEntity: Publication::class, inversedBy: "commentaires")]
    #[ORM\JoinColumn(name: 'publicationId', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Publication $publicationId;

    #[ORM\ManyToOne(targetEntity: Users::class, inversedBy: "commentaires")]
    #[ORM\JoinColumn(name: 'authorId', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: true)]
    private ?Users $authorId = null;

    #[ORM\Column(type: "datetime", name: "dateCreation")]
    private \DateTimeInterface $dateCreation;

    #[ORM\Column(type: "datetime", nullable: true, name: "dateModification")]
    private ?\DateTimeInterface $dateModification = null;

    #[ORM\Column(type: "string")]
    private string $statut = 'ACTIF';

    #[ORM\Column(type: "integer", name: "nombreReactions")]
    private int $nombreReactions = 0;

    #[ORM\Column(type: "string", length: 500, nullable: true, name: "gifUrl")]
    private ?string $gifUrl = null;

    public function getId() { return $this->id; }
    public function setId($value) { $this->id = $value; }

    public function getContenu() { return $this->contenu; }
    public function setContenu($value) { $this->contenu = $value; }

    public function getPublicationId() { return $this->publicationId; }
    public function setPublicationId($value) { $this->publicationId = $value; }

    public function getAuthorId() { return $this->authorId; }
    public function setAuthorId($value) { $this->authorId = $value; }

    public function getDateCreation() { return $this->dateCreation; }
    public function setDateCreation($value) { $this->dateCreation = $value; }

    public function getDateModification() { return $this->dateModification; }
    public function setDateModification($value) { $this->dateModification = $value; }

    public function getStatut() { return $this->statut; }
    public function setStatut($value) { $this->statut = $value; }

    public function getNombreReactions() { return $this->nombreReactions; }
    public function setNombreReactions($value) { $this->nombreReactions = $value; }

    public function getGifUrl() { return $this->gifUrl; }
    public function setGifUrl($value) { $this->gifUrl = $value; }

    #[ORM\OneToMany(mappedBy: "commentaireId", targetEntity: Reaction::class)]
    private Collection $reactions;

    public function getReactions(): Collection { return $this->reactions; }

    public function addReaction(Reaction $reaction): self
    {
        if (!$this->reactions->contains($reaction)) {
            $this->reactions[] = $reaction;
            $reaction->setCommentaireId($this);
        }
        return $this;
    }

    public function removeReaction(Reaction $reaction): self
    {
        if ($this->reactions->removeElement($reaction)) {
            if ($reaction->getCommentaireId() === $this) {
                $reaction->setCommentaireId(null);
            }
        }
        return $this;
    }

    #[ORM\OneToMany(mappedBy: "relatedCommentaireId", targetEntity: Notification::class)]
    private Collection $notifications;

    public function getNotifications(): Collection { return $this->notifications; }

    public function addNotification(Notification $notification): self
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications[] = $notification;
            $notification->setRelatedCommentaireId($this);
        }
        return $this;
    }

    public function removeNotification(Notification $notification): self
    {
        if ($this->notifications->removeElement($notification)) {
            if ($notification->getRelatedCommentaireId() === $this) {
                $notification->setRelatedCommentaireId(null);
            }
        }
        return $this;
    }
}
