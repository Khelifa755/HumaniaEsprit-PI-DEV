<?php

namespace App\Event;

use App\Entity\Offboarding;
use App\Entity\Utilisateur;
use Symfony\Contracts\EventDispatcher\Event;

class OffboardingCompletedEvent extends Event
{
    public function __construct(
        private readonly Utilisateur $utilisateur,
        private readonly Offboarding $offboarding
    ) {}

    public function getUtilisateur(): Utilisateur
    {
        return $this->utilisateur;
    }

    public function getOffboarding(): Offboarding
    {
        return $this->offboarding;
    }
}
