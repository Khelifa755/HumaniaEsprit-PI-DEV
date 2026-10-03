<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class Document
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'string', length: 500)]
    private string $path;

    #[ORM\Column(type: 'string', length: 100)]
    private string $type;

    #[ORM\Column(name: 'uploaded_at', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $uploadedAt = null;

    public function __construct()
    {
        $this->name = '';
        $this->path = '';
        $this->type = '';
    }

    #[ORM\PrePersist]
    public function setUploadedAtOnCreate(): void
    {
        if ($this->uploadedAt === null) {
            $this->uploadedAt = new \DateTime();
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): static
    {
        $this->path = $path;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getUploadedAt(): ?\DateTimeInterface
    {
        return $this->uploadedAt;
    }

    public function setUploadedAt(?\DateTimeInterface $uploadedAt): static
    {
        $this->uploadedAt = $uploadedAt;

        return $this;
    }
}
