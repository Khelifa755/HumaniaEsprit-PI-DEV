<?php

namespace App\Service\SOCIALMEDIA;

use App\Entity\Employe;
use App\Entity\Evenement;

final class PostGeneratorService
{
    public function onboarding(Employe $e): string
    {
        return sprintf('🎉 Bienvenue %s dans l\'équipe !', $e->getMatricule());
    }

    public function offboarding(Employe $e): string
    {
        return sprintf('👋 Merci %s pour sa contribution !', $e->getMatricule());
    }

    public function event(Evenement $event): string
    {
        return sprintf(
            "📢 Nouvel événement : %s\n\n📝 %s\n\n📍 Lieu : %s\n🗓️ Début : %s\n🕐 Fin : %s\n👥 Participants max : %d",
            $event->getTitre(),
            $event->getDescription(),
            $event->getLieu(),
            $event->getDateHeureDebut()->format('d/m/Y H:i'),
            $event->getDateHeureFin()->format('d/m/Y H:i'),
            $event->getNbParticipantsMax()
        );
    }
}

