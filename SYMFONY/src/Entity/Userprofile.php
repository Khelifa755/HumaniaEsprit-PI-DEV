<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Users;

#[ORM\Entity]
class Userprofile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Users::class, inversedBy: "userprofiles")]
    #[ORM\JoinColumn(name: 'userId', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Users $userId;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $bio = null;

    #[ORM\Column(type: "string", length: 500, nullable: true, name: "avatarUrl")]
    private ?string $avatarUrl = null;

    #[ORM\Column(type: "string", length: 500, nullable: true, name: "coverUrl")]
    private ?string $coverUrl = null;

    // [DEPRECATED] pas de vraie source de données
    #[ORM\Column(type: "string", length: 100, nullable: true)]
    private ?string $location = null;

    // [DEPRECATED] pas de vraie source de données
    #[ORM\Column(type: "string", length: 200, nullable: true)]
    private ?string $website = null;

    // [DEPRECATED] délègue vers utilisateur.departement
    #[ORM\Column(type: "string", length: 100, nullable: true)]
    private ?string $company = null;

    // [DEPRECATED] délègue vers utilisateur.posteActuel
    #[ORM\Column(type: "string", length: 100, nullable: true, name: "jobTitle")]
    private ?string $jobTitle = null;

    #[ORM\Column(type: "integer", name: "followersCount")]
    private int $followersCount = 0;

    #[ORM\Column(type: "integer", name: "followingCount")]
    private int $followingCount = 0;

    #[ORM\Column(type: "integer", name: "postsCount")]
    private int $postsCount = 0;

    // ── Getters de base ──────────────────────────────────────────

    public function getId() { return $this->id; }
    public function setId($value) { $this->id = $value; }

    public function getUserId() { return $this->userId; }
    public function setUserId($value) { $this->userId = $value; }

    public function getBio() { return $this->bio; }
    public function setBio(?string $value) { $this->bio = $value; }

    public function getCoverUrl() { return $this->coverUrl; }
    public function setCoverUrl(?string $value) { $this->coverUrl = $value; }

    public function getLocation() { return $this->location; }
    public function setLocation(?string $value) { $this->location = $value; }

    public function getWebsite() { return $this->website; }
    public function setWebsite(?string $value) { $this->website = $value; }

    public function setCompany(?string $value) { $this->company = $value; }
    public function setJobTitle(?string $value) { $this->jobTitle = $value; }

    public function getFollowersCount() { return $this->followersCount; }
    public function setFollowersCount($value) { $this->followersCount = $value; }

    public function getFollowingCount() { return $this->followingCount; }
    public function setFollowingCount($value) { $this->followingCount = $value; }

    public function getPostsCount() { return $this->postsCount; }
    public function setPostsCount($value) { $this->postsCount = $value; }

    // ── Délégation vers utilisateur (comme le JavaFX) ────────────

    /**
     * Avatar : préfère utilisateur.pdp, fallback sur userprofile.avatarUrl
     */
    public function getAvatarUrl(): ?string
    {
        $user = $this->userId;
        if ($user && $user->getAvatarUrl()) {
            return $user->getAvatarUrl(); // utilisateur.pdp
        }
        return $this->avatarUrl;
    }

    public function setAvatarUrl(?string $value): void
    {
        $this->avatarUrl = $value;
        // sync vers utilisateur.pdp
        if ($this->userId) {
            $this->userId->setAvatarUrl($value);
        }
    }

    /**
     * JobTitle : délègue vers utilisateur.posteActuel
     */
    public function getJobTitle(): ?string
    {
        $user = $this->userId;
        if ($user && $user->getPosteActuel()) {
            return $user->getPosteActuel();
        }
        return $this->jobTitle;
    }

    /**
     * Company : délègue vers utilisateur.departement
     */
    public function getCompany(): ?string
    {
        $user = $this->userId;
        if ($user && $user->getDepartement()) {
            return $user->getDepartement();
        }
        return $this->company;
    }

    // ── Helpers utiles dans les templates ────────────────────────

    public function getFullName(): string
    {
        $user = $this->userId;
        if ($user) {
            return trim(($user->getFirstName() ?? '') . ' ' . ($user->getLastName() ?? ''));
        }
        return '';
    }

    public function getInitials(): string
    {
        $user = $this->userId;
        if ($user) {
            $f = $user->getFirstName() ? strtoupper($user->getFirstName()[0]) : '';
            $l = $user->getLastName()  ? strtoupper($user->getLastName()[0])  : '';
            return $f . $l ?: '?';
        }
        return '?';
    }

    public function getUsername(): ?string
    {
        return $this->userId?->getUsername();
    }

    public function getEmail(): ?string
    {
        return $this->userId?->getEmail();
    }

    public function isOnline(): bool
    {
        return $this->userId?->getIsOnline() ?? false;
    }
}