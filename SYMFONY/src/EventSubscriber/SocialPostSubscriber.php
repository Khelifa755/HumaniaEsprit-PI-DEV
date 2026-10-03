<?php

namespace App\EventSubscriber;

use App\Entity\Publication;
use App\Event\OnboardingCompletedEvent;
use App\Event\OffboardingCompletedEvent;
use App\Event\EventCreatedEvent;
use App\Repository\SOCIALMEDIA\PublicationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class SocialPostSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PublicationRepository $publicationRepository,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            OnboardingCompletedEvent::class => 'onOnboardingCompleted',
            OffboardingCompletedEvent::class => 'onOffboardingCompleted',
            EventCreatedEvent::class => 'onEventCreated',
        ];
    }

    public function onOnboardingCompleted(OnboardingCompletedEvent $event): void
    {
        $utilisateur = $event->getUtilisateur();
        $onboarding = $event->getOnboarding();

        if ($onboarding->isPostPublished()) {
            return;
        }

        $name = trim($utilisateur->getPrenom() . ' ' . $utilisateur->getNom());
        $content = "🎉 Bienvenue {$name} dans l'équipe !";

        $employee = $this->entityManager->getRepository(\App\Entity\Employe::class)->find($utilisateur->getId());

        $this->persistPost(
            type: 'employee_onboarding_completed',
            content: $content,
            employee: $employee
        );

        $onboarding->setPostPublished(true);
        $utilisateur->setStatut('Actif');
        $this->entityManager->flush();
    }

    public function onOffboardingCompleted(OffboardingCompletedEvent $event): void
    {
        $utilisateur = $event->getUtilisateur();
        $offboarding = $event->getOffboarding();

        if ($offboarding->isPostPublished()) {
            return;
        }

        $name = trim($utilisateur->getPrenom() . ' ' . $utilisateur->getNom());
        $content = "👋 Merci {$name} pour sa contribution !";

        $employee = $this->entityManager->getRepository(\App\Entity\Employe::class)->find($utilisateur->getId());

        $this->persistPost(
            type: 'employee_offboarding_completed',
            content: $content,
            employee: $employee
        );

        $offboarding->setPostPublished(true);
        $utilisateur->setStatut('Inactif');
        $this->entityManager->flush();
    }

    public function onEventCreated(EventCreatedEvent $event): void
    {
        $evenement = $event->getEvent();
        if ($this->publicationRepository->existsByEvent($evenement)) {
            return;
        }

        $content = sprintf(
            "📢 Nouvel événement : %s\n\n📝 %s\n\n📍 Lieu : %s\n🗓️ Début : %s\n🕐 Fin : %s\n👥 Participants max : %d",
            $evenement->getTitre(),
            $evenement->getDescription(),
            $evenement->getLieu(),
            $evenement->getDateHeureDebut()->format('d/m/Y H:i'),
            $evenement->getDateHeureFin()->format('d/m/Y H:i'),
            $evenement->getNbParticipantsMax()
        );

        $this->persistPost(
            type: 'event_created',
            content: $content,
            evenement: $evenement
        );
    }

    private function persistPost(
        string $type,
        string $content,
        ?\App\Entity\Employe $employee = null,
        ?\App\Entity\Evenement $evenement = null,
    ): void {
        $post = new Publication();
        $post->setType($type);
        $post->setContent($content);
        $post->setContenu($content);
        $post->setStatut('ACTIF');
        $post->setVisibility('PUBLIC');
        $post->setDateCreation(new \DateTime());
        $post->setRelatedEmployee($employee);
        $post->setRelatedEvent($evenement);

        $this->entityManager->persist($post);
        $this->entityManager->flush();
    }
}
