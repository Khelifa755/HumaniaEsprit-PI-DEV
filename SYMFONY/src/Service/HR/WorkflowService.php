<?php

namespace App\Service\HR;

use App\Entity\Onboarding;
use App\Entity\Offboarding;
use App\Event\OnboardingCompletedEvent;
use App\Event\OffboardingCompletedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class WorkflowService
{
    public function __construct(
        private EntityManagerInterface $em,
        private EventDispatcherInterface $dispatcher
    ) {}

    public function completeOnboarding(Onboarding $onboarding): void
    {
        if ($onboarding->getStatus() === 'completed') {
            return;
        }

        $onboarding->setStatus('completed');
        $onboarding->setCompletedAt(new \DateTime());
        
        $this->em->flush();

        // Trigger event AFTER Doctrine flush
        $this->dispatcher->dispatch(new OnboardingCompletedEvent($onboarding->getUtilisateur(), $onboarding));
    }

    public function completeOffboarding(Offboarding $offboarding): void
    {
        if ($offboarding->getStatus() === 'completed' || $offboarding->getStatus() === 'archived') {
            return;
        }

        $offboarding->setStatus('completed');
        $offboarding->setCompletedAt(new \DateTime());

        $this->em->flush();

        // Trigger event AFTER Doctrine flush
        $this->dispatcher->dispatch(new OffboardingCompletedEvent($offboarding->getUtilisateur(), $offboarding));
    }

    public function archiveOffboarding(Offboarding $offboarding): void
    {
        if ($offboarding->getStatus() !== 'completed') {
            throw new \LogicException('L\'offboarding doit être complété avant d\'être archivé.');
        }

        if (!$offboarding->isPostPublished()) {
            throw new \LogicException('La publication doit être effectuée avant d\'archiver l\'offboarding.');
        }

        $offboarding->setStatus('archived');
        $this->em->flush();
    }
}
