<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Users;

#[ORM\Entity]
#[ORM\Table(name: 'share')]
class Share
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Users::class)]
    #[ORM\JoinColumn(name: 'userId', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Users $userId;

    #[ORM\ManyToOne(targetEntity: Publication::class)]
    #[ORM\JoinColumn(name: 'publicationId', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Publication $publicationId;

    #[ORM\Column(type: "datetime", name: "sharedAt")]
    private \DateTimeInterface $sharedAt;

    #[ORM\Column(type: "text", nullable: true, name: "sharedMessage")]
    private ?string $sharedMessage = null;

    #[ORM\Column(type: "integer", name: "shareCount")]
    private int $shareCount = 0;

    public function getId(): ?int { return $this->id; }
    public function setId(int $id): self { $this->id = $id; return $this; }

    public function getUserId(): ?Users { return $this->userId; }
    public function setUserId(?Users $userId): self { $this->userId = $userId; return $this; }

    public function getPublicationId(): ?Publication { return $this->publicationId; }
    public function setPublicationId(?Publication $publicationId): self { $this->publicationId = $publicationId; return $this; }

    public function getSharedAt(): ?\DateTimeInterface { return $this->sharedAt; }
    public function setSharedAt(\DateTimeInterface $sharedAt): self { $this->sharedAt = $sharedAt; return $this; }

    public function getSharedMessage(): ?string { return $this->sharedMessage; }
    public function setSharedMessage(?string $sharedMessage): self { $this->sharedMessage = $sharedMessage; return $this; }

    public function getShareCount(): ?int { return $this->shareCount; }
    public function setShareCount(int $shareCount): self { $this->shareCount = $shareCount; return $this; }
}
