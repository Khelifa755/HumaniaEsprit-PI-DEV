<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'offboarding')]
class Offboarding
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Utilisateur $utilisateur = null;

    #[ORM\Column(type: 'string', length: 50, options: ['default' => 'pending'])]
    private string $status = 'pending';

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $startedAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $completedAt = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $postPublished = false;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $canArchive = false;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $reason = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $departureDate = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    #[ORM\OneToMany(mappedBy: 'offboarding', targetEntity: OffboardingTask::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $tasks;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->tasks = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): self
    {
        $this->utilisateur = $utilisateur;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getStartedAt(): ?\DateTimeInterface
    {
        return $this->startedAt;
    }

    public function setStartedAt(?\DateTimeInterface $startedAt): self
    {
        $this->startedAt = $startedAt;
        return $this;
    }

    public function getCompletedAt(): ?\DateTimeInterface
    {
        return $this->completedAt;
    }

    public function setCompletedAt(?\DateTimeInterface $completedAt): self
    {
        $this->completedAt = $completedAt;
        return $this;
    }

    public function isPostPublished(): bool
    {
        return $this->postPublished;
    }

    public function setPostPublished(bool $postPublished): self
    {
        $this->postPublished = $postPublished;
        return $this;
    }

    public function isCanArchive(): bool
    {
        return $this->canArchive;
    }

    public function setCanArchive(bool $canArchive): self
    {
        $this->canArchive = $canArchive;
        return $this;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(?string $reason): self
    {
        $this->reason = $reason;
        return $this;
    }

    public function getDepartureDate(): ?\DateTimeInterface
    {
        return $this->departureDate;
    }

    public function setDepartureDate(?\DateTimeInterface $departureDate): self
    {
        $this->departureDate = $departureDate;
        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    public function addTask(OffboardingTask $task): self
    {
        if (!$this->tasks->contains($task)) {
            $this->tasks[] = $task;
            $task->setOffboarding($this);
        }
        return $this;
    }

    public function removeTask(OffboardingTask $task): self
    {
        if ($this->tasks->removeElement($task)) {
            if ($task->getOffboarding() === $this) {
                $task->setOffboarding(null);
            }
        }
        return $this;
    }
}
