<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'onboarding_task')]
class OnboardingTask
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Onboarding::class, inversedBy: 'tasks')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Onboarding $onboarding = null;

    #[ORM\Column(type: 'string', length: 500)]
    private string $label = '';

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isDone = false;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOnboarding(): ?Onboarding
    {
        return $this->onboarding;
    }

    public function setOnboarding(?Onboarding $onboarding): self
    {
        $this->onboarding = $onboarding;
        return $this;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;
        return $this;
    }

    public function isDone(): bool
    {
        return $this->isDone;
    }

    public function setIsDone(bool $isDone): self
    {
        $this->isDone = $isDone;
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
}
