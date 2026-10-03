<?php

namespace App\Controller\PLANNIFICATION;

use App\Entity\Onboarding;
use App\Entity\Offboarding;
use App\Entity\Utilisateur;
use App\Repository\Utilisateur\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/boarding', name: 'app_boarding_')]
final class BoardingHistoryController extends AbstractController
{
    #[Route('/history', name: 'history', methods: ['GET'])]
    public function history(EntityManagerInterface $em): Response
    {
        $onboardings  = $em->getRepository(Onboarding::class)->findAll();
        $offboardings = $em->getRepository(Offboarding::class)->findAll();

        $history = [];

        foreach ($onboardings as $onboarding) {
            $tasks = $onboarding->getTasks();
            $total = count($tasks);
            $done = 0;
            foreach ($tasks as $task) {
                if ($task->isDone()) $done++;
            }

            $history[] = [
                'type' => 'onboarding',
                'id' => $onboarding->getId(),
                'status' => $onboarding->getStatus(),
                'utilisateur' => $onboarding->getUtilisateur(),
                'startedAt' => $onboarding->getStartedAt(),
                'completedAt' => $onboarding->getCompletedAt(),
                'progress' => [
                    'done' => $done,
                    'total' => $total,
                    'percentage' => $total > 0 ? round(($done / $total) * 100) : 0
                ],
                'detail' => $onboarding->getDepartement() ?? '—'
            ];
        }

        foreach ($offboardings as $offboarding) {
            $tasks = $offboarding->getTasks();
            $total = count($tasks);
            $done = 0;
            foreach ($tasks as $task) {
                if ($task->isDone()) $done++;
            }

            $history[] = [
                'type' => 'offboarding',
                'id' => $offboarding->getId(),
                'status' => $offboarding->getStatus(),
                'utilisateur' => $offboarding->getUtilisateur(),
                'startedAt' => $offboarding->getStartedAt(),
                'completedAt' => $offboarding->getCompletedAt(),
                'progress' => [
                    'done' => $done,
                    'total' => $total,
                    'percentage' => $total > 0 ? round(($done / $total) * 100) : 0
                ],
                'detail' => $offboarding->getReason() ?? '—'
            ];
        }

        usort($history, function($a, $b) {
            $dateA = $a['startedAt'] ?? new \DateTime();
            $dateB = $b['startedAt'] ?? new \DateTime();
            return $dateB <=> $dateA;
        });

        return $this->render('boarding/history.html.twig', [
            'history' => $history
        ]);
    }
}
