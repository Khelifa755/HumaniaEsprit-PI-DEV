<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Groups;
use Doctrine\Common\Collections\Collection;
use App\Entity\Savedpost;

#[ORM\Entity]
#[ORM\Table(name: 'publication')]
class Publication
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "text")]
    private string $contenu;

    #[ORM\ManyToOne(targetEntity: Users::class, inversedBy: "publications")]
    #[ORM\JoinColumn(name: 'authorId', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: true)]
    private ?Users $authorId = null;

    #[ORM\Column(type: "datetime", name: "dateCreation")]
    private \DateTimeInterface $dateCreation;

    #[ORM\Column(type: "datetime", nullable: true, name: "dateModification")]
    private ?\DateTimeInterface $dateModification = null;

    #[ORM\Column(type: "string")]
    private string $statut;

    #[ORM\Column(type: "string", length: 500, nullable: true, name: "imageUrl")]
    private ?string $imageUrl = null;

    #[ORM\Column(type: "string", length: 500, nullable: true, name: "gifUrl")]
    private ?string $gifUrl = null;

    #[ORM\Column(type: "integer", name: "nombreCommentaires")]
    private int $nombreCommentaires = 0;

    #[ORM\Column(type: "integer", name: "nombreReactions")]
    private int $nombreReactions = 0;

    #[ORM\ManyToOne(targetEntity: Publication::class, inversedBy: "publications")]
    #[ORM\JoinColumn(name: 'sharedFromId', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private ?Publication $sharedFromId = null;

    #[ORM\Column(type: "string", length: 500, nullable: true, name: "shareMessage")]
    private ?string $shareMessage = null;

    #[ORM\ManyToOne(targetEntity: Groups::class, inversedBy: "publications")]
    #[ORM\JoinColumn(name: 'groupId', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private ?Groups $groupId = null;

    #[ORM\Column(type: "string", length: 20)]
    private string $visibility = 'PUBLIC';

    #[ORM\Column(type: 'string', length: 50, options: ['default' => 'generic'])]
    private string $type = 'generic';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $content = null;

    #[ORM\ManyToOne(targetEntity: Employe::class)]
    #[ORM\JoinColumn(name: 'related_employee_id', referencedColumnName: 'utilisateur_id', nullable: true, onDelete: 'SET NULL')]
    private ?Employe $relatedEmployee = null;

    #[ORM\ManyToOne(targetEntity: Evenement::class)]
    #[ORM\JoinColumn(name: 'related_event_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Evenement $relatedEvent = null;

    #[ORM\OneToMany(mappedBy: "publicationId", targetEntity: Commentaire::class)]
    private Collection $commentaires;

    #[ORM\OneToMany(mappedBy: "publicationId", targetEntity: Savedpost::class)]
    private Collection $savedposts;

    #[ORM\OneToMany(mappedBy: "sharedFromId", targetEntity: Publication::class)]
    private Collection $publications;

    #[ORM\OneToMany(mappedBy: "publicationId", targetEntity: Reaction::class)]
    private Collection $reactions;

    #[ORM\OneToMany(mappedBy: "relatedPublicationId", targetEntity: Notification::class)]
    private Collection $notifications;

    #[ORM\OneToMany(mappedBy: "publicationId", targetEntity: Mention::class)]
    private Collection $mentions;

    public function getId()
    {
        return $this->id;
    }
    public function setId($value)
    {
        $this->id = $value;
    }

    public function getContenu()
    {
        return $this->contenu;
    }
    public function setContenu($value)
    {
        $this->contenu = $value;
    }

    public function getAuthorId()
    {
        return $this->authorId;
    }
    public function setAuthorId($value)
    {
        $this->authorId = $value;
    }

    public function getDateCreation()
    {
        return $this->dateCreation;
    }
    public function setDateCreation($value)
    {
        $this->dateCreation = $value;
    }

    public function getDateModification()
    {
        return $this->dateModification;
    }
    public function setDateModification($value)
    {
        $this->dateModification = $value;
    }

    public function getStatut()
    {
        return $this->statut;
    }
    public function setStatut($value)
    {
        $this->statut = $value;
    }

    public function getImageUrl()
    {
        return $this->imageUrl;
    }
    public function setImageUrl($value)
    {
        $this->imageUrl = $value;
    }

    public function getGifUrl()
    {
        return $this->gifUrl;
    }
    public function setGifUrl($value)
    {
        $this->gifUrl = $value;
    }

    public function getNombreCommentaires()
    {
        return $this->nombreCommentaires;
    }
    public function setNombreCommentaires($value)
    {
        $this->nombreCommentaires = $value;
    }

    public function getNombreReactions()
    {
        return $this->nombreReactions;
    }
    public function setNombreReactions($value)
    {
        $this->nombreReactions = $value;
    }

    public function getSharedFromId()
    {
        return $this->sharedFromId;
    }
    public function setSharedFromId($value)
    {
        $this->sharedFromId = $value;
    }

    public function getShareMessage()
    {
        return $this->shareMessage;
    }
    public function setShareMessage($value)
    {
        $this->shareMessage = $value;
    }

    public function getGroupId()
    {
        return $this->groupId;
    }
    public function setGroupId($value)
    {
        $this->groupId = $value;
    }

    public function getVisibility()
    {
        return $this->visibility;
    }
    public function setVisibility($value)
    {
        $this->visibility = $value;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getRelatedEmployee(): ?Employe
    {
        return $this->relatedEmployee;
    }

    public function setRelatedEmployee(?Employe $employee): self
    {
        $this->relatedEmployee = $employee;

        return $this;
    }

    public function getRelatedEvent(): ?Evenement
    {
        return $this->relatedEvent;
    }

    public function setRelatedEvent(?Evenement $event): self
    {
        $this->relatedEvent = $event;

        return $this;
    }

    public function getCommentaires(): Collection
    {
        return $this->commentaires;
    }
    public function getSavedposts(): Collection
    {
        return $this->savedposts;
    }
    public function getPublications(): Collection
    {
        return $this->publications;
    }
    public function getReactions(): Collection
    {
        return $this->reactions;
    }
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }
    public function getMentions(): Collection
    {
        return $this->mentions;
    }

    public function addNotification(Notification $notification): self
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications[] = $notification;
            $notification->setRelatedPublicationId($this);
        }
        return $this;
    }

    public function removeNotification(Notification $notification): self
    {
        if ($this->notifications->removeElement($notification)) {
            if ($notification->getRelatedPublicationId() === $this) {
                $notification->setRelatedPublicationId(null);
            }
        }
        return $this;
    }
}