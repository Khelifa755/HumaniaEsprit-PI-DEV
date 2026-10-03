<?php

namespace App\Event;

use App\Entity\Onboarding;
use App\Entity\Utilisateur;
use Symfony\Contracts\EventDispatcher\Event;

class OnboardingCompletedEvent extends Event
{
    public function __construct(
        private readonly Utilisateur $utilisateur,
        private readonly Onboarding $onboarding
    ) {}

    public function getUtilisateur(): Utilisateur
    {
        return $this->utilisateur;
    }

    public function getOnboarding(): Onboarding
    {
        return $this->onboarding;
    }
}
