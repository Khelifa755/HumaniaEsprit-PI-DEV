<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Onboarding
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $utilisateur = null;

    #[ORM\Column(type: 'string', length: 50, options: ['default' => 'pending'])]
    private string $status = 'pending';

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $startedAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $completedAt = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $postPublished = false;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $departement = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $arrivalDate = null;

    #[ORM\OneToMany(mappedBy: 'onboarding', targetEntity: OnboardingTask::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $tasks;

    public function __construct()
    {
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

    public function getDepartement(): ?string
    {
        return $this->departement;
    }

    public function setDepartement(?string $departement): self
    {
        $this->departement = $departement;
        return $this;
    }

    public function getArrivalDate(): ?\DateTimeInterface
    {
        return $this->arrivalDate;
    }

    public function setArrivalDate(?\DateTimeInterface $arrivalDate): self
    {
        $this->arrivalDate = $arrivalDate;
        return $this;
    }

    /**
     * @return Collection<int, OnboardingTask>
     */
    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    public function addTask(OnboardingTask $task): self
    {
        if (!$this->tasks->contains($task)) {
            $this->tasks[] = $task;
            $task->setOnboarding($this);
        }

        return $this;
    }

    public function removeTask(OnboardingTask $task): self
    {
        if ($this->tasks->removeElement($task)) {
            if ($task->getOnboarding() === $this) {
                $task->setOnboarding(null);
            }
        }

        return $this;
    }
}
