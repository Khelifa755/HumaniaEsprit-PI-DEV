<?php

namespace App\Controller\PLANNIFICATION;

use App\Entity\Evenement;
use App\Entity\Publication;
use App\Event\EventCreatedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/evenement')]
final class EvenementController extends AbstractController
{
    public function __construct(
        private readonly EventDispatcherInterface $dispatcher,
    ) {}

    // ── LIST ──────────────────────────────────────────────────────────────────

    #[Route(name: 'app_evenement_index', methods: ['GET'])]
    public function index(EntityManagerInterface $em): Response
    {
        return $this->render('PLANNIFICATION/evenement/index.html.twig', [
            'evenements' => $em->getRepository(Evenement::class)->findAll(),
        ]);
    }

    // ── CREATE ────────────────────────────────────────────────────────────────

    #[Route('/new', name: 'app_evenement_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $evenement = new Evenement();
        $this->hydrate($evenement, $request);
        $evenement->setCreeLe(new \DateTimeImmutable());
        $evenement->setCreePar('');
        $evenement->setParticipantsInscrits('');

        $em->persist($evenement);
        $em->flush();
        $this->dispatcher->dispatch(new EventCreatedEvent($evenement));

        $this->addFlash('success', 'Événement « ' . $evenement->getTitre() . ' » créé avec succès.');
        return $this->redirectToRoute('app_evenement_index', [], Response::HTTP_SEE_OTHER);
    }

    // ── EDIT ──────────────────────────────────────────────────────────────────

    #[Route('/{id}/edit', name: 'app_evenement_edit', methods: ['POST'])]
    public function edit(Request $request, Evenement $evenement, EntityManagerInterface $em): Response
    {
        $this->hydrate($evenement, $request);
        $em->flush();

        $this->addFlash('success', 'Événement « ' . $evenement->getTitre() . ' » mis à jour.');
        return $this->redirectToRoute('app_evenement_index', [], Response::HTTP_SEE_OTHER);
    }

    // ── DELETE ────────────────────────────────────────────────────────────────

    // Kept on /{id} (POST) to preserve original route name + URL.
    #[Route('/{id}', name: 'app_evenement_delete', methods: ['POST'])]
    public function delete(Request $request, Evenement $evenement, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $evenement->getId(), $request->getPayload()->getString('_token'))) {
            $titre = $evenement->getTitre();

            // Remove related publications so the feed doesn't break
            $publications = $em->getRepository(Publication::class)
                ->createQueryBuilder('p')
                ->where('p.relatedEvent = :ev')
                ->setParameter('ev', $evenement)
                ->getQuery()
                ->getResult();

            foreach ($publications as $pub) {
                $em->remove($pub);
            }
            $em->flush(); // persist the deletions before deleting the event

            $em->remove($evenement);
            $em->flush();
            $this->addFlash('success', 'Événement « ' . $titre . ' » supprimé.');
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('app_evenement_index', [], Response::HTTP_SEE_OTHER);
    }

    // ── Hydration ─────────────────────────────────────────────────────────────

    private function hydrate(Evenement $ev, Request $r): void
    {
        $ev->setTitre(trim($r->request->get('titre', '')));
        $ev->setDescription(trim($r->request->get('description', '')));
        $ev->setLieu(trim($r->request->get('lieu', '')));
        $ev->setNbParticipantsMax((int) $r->request->get('nbParticipantsMax', 1));

        $debut = $r->request->get('dateHeureDebut');
        $fin = $r->request->get('dateHeureFin');

        $debutDt = $debut ? new \DateTimeImmutable($debut) : new \DateTimeImmutable();
        $finDt = $fin ? new \DateTimeImmutable($fin) : $debutDt->modify('+1 hour');

        $ev->setDateHeureDebut($debutDt);
        $ev->setDateHeureFin($finDt);
        // dateEvenement = date portion of début
        $ev->setDateEvenement(\DateTimeImmutable::createFromFormat('Y-m-d', $debutDt->format('Y-m-d')));
    }
}