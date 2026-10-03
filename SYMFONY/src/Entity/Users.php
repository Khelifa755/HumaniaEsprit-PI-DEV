<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\Notification;

#[ORM\Entity]
#[ORM\Table(name: 'utilisateur')]
class Users
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 50, name: "username")]
    private string $username;

    #[ORM\Column(type: "string", length: 100, name: "email")]
    private string $email;

    #[ORM\Column(type: "string", length: 255, name: "mot_de_passe", nullable: true)]
    private ?string $password = null;

    #[ORM\Column(type: "string", length: 100, name: "prenom", nullable: true)]
    private ?string $firstName = null;

    #[ORM\Column(type: "string", length: 100, name: "nom", nullable: true)]
    private ?string $lastName = null;

    // bio n'existe pas dans utilisateur — vient de userprofile
    private ?string $bio = null;

    #[ORM\Column(type: "string", length: 255, nullable: true, name: "pdp")]
    private ?string $avatarUrl = null;

    #[ORM\Column(type: "datetime", name: "date_creation", nullable: true)]
    private ?\DateTimeInterface $createdAt = null;

    // updatedAt n'existe pas dans utilisateur
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(type: "string", length: 50, name: "statut", nullable: true)]
    private string $status = 'Actif';

    #[ORM\Column(type: "string", length: 20, name: "role", nullable: true)]
    private string $role = 'EMPLOYE';

    #[ORM\Column(type: "datetime", nullable: true, name: "last_seen")]
    private ?\DateTimeInterface $lastSeen = null;

    #[ORM\Column(type: "boolean", name: "is_online", nullable: true)]
    private bool $isOnline = false;

    #[ORM\OneToMany(mappedBy: "followerId", targetEntity: Follow::class)]
    private Collection $followings;

    #[ORM\OneToMany(mappedBy: "followingId", targetEntity: Follow::class)]
    private Collection $followers;

    #[ORM\OneToMany(mappedBy: "userId", targetEntity: Notification::class)]
    private Collection $notifications;

    #[ORM\OneToMany(mappedBy: "relatedUserId", targetEntity: Notification::class)]
    private Collection $relatedNotifications;

    #[ORM\OneToMany(mappedBy: "createdById", targetEntity: Groups::class)]
    private Collection $groupss;

    #[ORM\OneToMany(mappedBy: "userId", targetEntity: Userprofile::class)]
    private Collection $userprofiles;

    #[ORM\OneToMany(mappedBy: "authorId", targetEntity: Commentaire::class)]
    private Collection $commentaires;

    #[ORM\OneToMany(mappedBy: "userId", targetEntity: Groupmember::class)]
    private Collection $groupmembers;

    #[ORM\OneToMany(mappedBy: "userId", targetEntity: Savedpost::class)]
    private Collection $savedposts;

    #[ORM\OneToMany(mappedBy: "authorId", targetEntity: Publication::class)]
    private Collection $publications;

    #[ORM\OneToMany(mappedBy: "userId", targetEntity: Reaction::class)]
    private Collection $reactions;

    public function __construct()
    {
        $this->followings           = new ArrayCollection();
        $this->followers            = new ArrayCollection();
        $this->notifications        = new ArrayCollection();
        $this->relatedNotifications = new ArrayCollection();
        $this->groupss              = new ArrayCollection();
        $this->userprofiles         = new ArrayCollection();
        $this->commentaires         = new ArrayCollection();
        $this->groupmembers         = new ArrayCollection();
        $this->savedposts           = new ArrayCollection();
        $this->publications         = new ArrayCollection();
        $this->reactions            = new ArrayCollection();
    }

    public function getId() { return $this->id; }
    public function setId($value) { $this->id = $value; }

    public function getUsername() { return $this->username; }
    public function setUsername($value) { $this->username = $value; }

    public function getEmail() { return $this->email; }
    public function setEmail($value) { $this->email = $value; }

    public function getPassword() { return $this->password; }
    public function setPassword(?string $value) { $this->password = $value; }

    public function getFirstName() { return $this->firstName; }
    public function setFirstName(?string $value) { $this->firstName = $value; }

    public function getLastName() { return $this->lastName; }
    public function setLastName(?string $value) { $this->lastName = $value; }

    // bio vient de userprofile — pas de annotation ORM
    public function getBio() { return $this->bio; }
    public function setBio(?string $value) { $this->bio = $value; }

    public function getAvatarUrl() { return $this->avatarUrl; }
    public function setAvatarUrl(?string $value) { $this->avatarUrl = $value; }

    public function getStatus() { return $this->status; }
    public function setStatus(string $value) { $this->status = $value; }

    public function getCreatedAt() { return $this->createdAt; }
    public function setCreatedAt(?\DateTimeInterface $value) { $this->createdAt = $value; }

    // updatedAt pas dans utilisateur — pas de annotation ORM
    public function getUpdatedAt() { return $this->updatedAt; }
    public function setUpdatedAt(?\DateTimeInterface $value) { $this->updatedAt = $value; }

    public function isActive() { return $this->status === 'Actif'; }
    public function getIsActive() { return $this->isActive(); }
    public function setIsActive(bool $value) { $this->status = $value ? 'Actif' : 'Inactif'; }

    public function getRole() { return $this->role; }
    public function setRole(string $value) { $this->role = $value; }

    public function getLastSeen() { return $this->lastSeen; }
    public function setLastSeen(?\DateTimeInterface $value) { $this->lastSeen = $value; }

    public function getIsOnline() { return $this->isOnline; }
    public function setIsOnline(bool $value) { $this->isOnline = $value; }

    // helper utile dans les templates
    public function getFullName(): string 
    { 
        return trim(($this->firstName ?? '') . ' ' . ($this->lastName ?? '')); 
    }

    public function getInitials(): string
    {
        $f = $this->firstName ? strtoupper($this->firstName[0]) : '';
        $l = $this->lastName  ? strtoupper($this->lastName[0])  : '';
        return $f . $l ?: '?';
    }

    public function getFollowings(): Collection { return $this->followings; }
    public function getFollowers(): Collection { return $this->followers; }
    public function getNotifications(): Collection { return $this->notifications; }
    public function getRelatedNotifications(): Collection { return $this->relatedNotifications; }
    public function getGroupss(): Collection { return $this->groupss; }
    public function getUserprofiles(): Collection { return $this->userprofiles; }
    public function getCommentaires(): Collection { return $this->commentaires; }
    public function getGroupmembers(): Collection { return $this->groupmembers; }
    public function getSavedposts(): Collection { return $this->savedposts; }
    public function getPublications(): Collection { return $this->publications; }
    public function getReactions(): Collection { return $this->reactions; }
    #[ORM\Column(type: "string", length: 150, nullable: true, name: "posteActuel")]
private ?string $posteActuel = null;

#[ORM\Column(type: "string", length: 100, nullable: true, name: "departement")]
private ?string $departement = null;

public function getPosteActuel(): ?string { return $this->posteActuel; }
public function setPosteActuel(?string $value) { $this->posteActuel = $value; }

public function getDepartement(): ?string { return $this->departement; }
public function setDepartement(?string $value) { $this->departement = $value; }
}