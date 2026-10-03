<?php

namespace App\Controller\PLANNIFICATION;

use App\Entity\Espaces;
use App\Entity\Reservation_espaces;
use App\Form\PLANNIFICATION\ReservationEspacesType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/reservation-espaces')]
#[IsGranted('ROLE_USER')]
final class ReservationEspacesController extends AbstractController
{
    #[Route(name: 'app_reservation_espaces_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $espaces = $entityManager
            ->getRepository(Espaces::class)
            ->findAll();

        $today = new \DateTime('today');
        $occupiedTodayIds = [];

        foreach ($entityManager->getRepository(Reservation_espaces::class)->findBy(['dateReservation' => $today]) as $reservation) {
            $espace = $reservation->getIdEspace();
            if ($espace !== null) {
                $occupiedTodayIds[$espace->getId()] = true;
            }
        }

        return $this->render('PLANNIFICATION/reservation_espaces/index.html.twig', [
            'espaces' => $espaces,
            'occupiedTodayIds' => $occupiedTodayIds,
        ]);
    }

    #[Route('/{id}/availability', name: 'app_reservation_espaces_availability', methods: ['GET'])]
    public function availability(int $id, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        /** @var Espaces|null $espace */
        $espace = $entityManager->getRepository(Espaces::class)->find($id);
        $date = \DateTime::createFromFormat('Y-m-d', (string) $request->query->get('date'));

        if ($espace === null || $date === false) {
            return $this->json(['message' => 'Paramètres invalides.'], Response::HTTP_BAD_REQUEST);
        }

        if ($this->isCoworkingSpace($espace)) {
            return $this->json(['message' => 'Utilisez le plan des sièges pour le coworking.'], Response::HTTP_BAD_REQUEST);
        }

        $reservations = $entityManager->getRepository(Reservation_espaces::class)->findBy([
            'idEspace' => $espace,
            'dateReservation' => $date,
        ]);

        $takenByHour = [];
        foreach ($reservations as $reservation) {
            $hour = (int) $reservation->getDateHeureDebut()->format('G');
            $takenByHour[$hour] = $reservation;
        }

        $slots = [];
        $userId = $this->getAuthenticatedUserId();
        for ($hour = 8; $hour < 17; $hour++) {
            $reservation = $takenByHour[$hour] ?? null;
            $status = 'available';
            $label = sprintf('%02d:00-%02d:00', $hour, $hour + 1);
            $tooltip = null;

            if ($reservation instanceof Reservation_espaces) {
                if ((int) $reservation->getIdEmploye() === $userId) {
                    $status = 'mine';
                    $label .= ' - Moi';
                    $tooltip = 'Reserve par vous';
                } else {
                    $status = 'taken';
                    $label .= ' - Reserve';
                    $tooltip = 'Creneau deja reserve';
                }
            }

            $slots[] = [
                'hour' => $hour,
                'label' => $label,
                'status' => $status,
                'tooltip' => $tooltip,
                'objectif' => $reservation?->getObjectif(),
            ];
        }

        return $this->json([
            'espaceId' => $espace->getId(),
            'date' => $date->format('Y-m-d'),
            'slots' => $slots,
        ]);
    }

    #[Route('/new', name: 'app_reservation_espaces_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($this->hasQuickBookingPayload($request)) {
            return $this->handleQuickBooking($request, $entityManager);
        }

        $reservation = new Reservation_espaces();
        $reservation->setCreeLe(new \DateTimeImmutable());
        $form = $this->createForm(ReservationEspacesType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->hasResourceTimeConflict($entityManager, $reservation)) {
                $this->addFlash('error', 'This space is already reserved for the selected time slot');
                return $this->render('PLANNIFICATION/reservation_espaces/new.html.twig', [
                    'reservation' => $reservation,
                    'form' => $form,
                ]);
            }

            $reservation->setIdEmploye($this->getAuthenticatedUserId());
            $entityManager->persist($reservation);
            $entityManager->flush();

            return $this->redirectToRoute('app_reservation_espaces_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('PLANNIFICATION/reservation_espaces/new.html.twig', [
            'reservation' => $reservation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_reservation_espaces_show', methods: ['GET'])]
    public function show(Reservation_espaces $reservation): Response
    {
        $this->denyAccessUnlessGrantedToReservation($reservation);

        return $this->render('PLANNIFICATION/reservation_espaces/show.html.twig', [
            'reservation' => $reservation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_reservation_espaces_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reservation_espaces $reservation, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGrantedToReservation($reservation);

        $form = $this->createForm(ReservationEspacesType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->hasResourceTimeConflict($entityManager, $reservation, $reservation->getId())) {
                $this->addFlash('error', 'This space is already reserved for the selected time slot');
                return $this->render('PLANNIFICATION/reservation_espaces/edit.html.twig', [
                    'reservation' => $reservation,
                    'form' => $form,
                ]);
            }

            $reservation->setIdEmploye($this->getAuthenticatedUserId());
            $entityManager->flush();

            return $this->redirectToRoute('app_reservation_espaces_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('PLANNIFICATION/reservation_espaces/edit.html.twig', [
            'reservation' => $reservation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_reservation_espaces_delete', methods: ['POST'])]
    public function delete(Request $request, Reservation_espaces $reservation, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGrantedToReservation($reservation);

        if ($this->isCsrfTokenValid('delete'.$reservation->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($reservation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_reservation_espaces_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/calendar-feed', name: 'app_reservation_espaces_calendar_feed', methods: ['GET'])]
    public function calendarFeed(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $start = $request->query->get('start');
        $end = $request->query->get('end');

        $qb = $entityManager->getRepository(Reservation_espaces::class)->createQueryBuilder('r');
        $qb
            ->leftJoin('r.idEspace', 'e')
            ->andWhere('r.idEmploye = :userId')
            ->setParameter('userId', $this->getAuthenticatedUserId())
            ->orderBy('r.dateHeureDebut', 'ASC');

        if (is_string($start) && $start !== '') {
            $startDate = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, $start)
                ?: \DateTimeImmutable::createFromFormat('Y-m-d', $start);
            if ($startDate instanceof \DateTimeImmutable) {
                $qb->andWhere('r.dateHeureFin >= :start')->setParameter('start', $startDate);
            }
        }

        if (is_string($end) && $end !== '') {
            $endDate = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, $end)
                ?: \DateTimeImmutable::createFromFormat('Y-m-d', $end);
            if ($endDate instanceof \DateTimeImmutable) {
                $qb->andWhere('r.dateHeureDebut <= :end')->setParameter('end', $endDate);
            }
        }

        $events = array_map(
            static function (Reservation_espaces $reservation): array {
                $title = $reservation->getObjectif() ?: 'Réservation';
                $espace = $reservation->getIdEspace();

                return [
                    'id' => (string) $reservation->getId(),
                    'title' => $title,
                    'start' => $reservation->getDateHeureDebut()->format(\DateTimeInterface::ATOM),
                    'end' => $reservation->getDateHeureFin()->format(\DateTimeInterface::ATOM),
                    'allDay' => false,
                    'extendedProps' => [
                        'espace' => $espace?->getId(),
                        'espaceNom' => $espace?->getNom(),
                        'objectif' => $reservation->getObjectif(),
                        'statut' => $reservation->getStatut(),
                    ],
                ];
            },
            $qb->getQuery()->getResult()
        );

        return $this->json($events);
    }

    private function hasQuickBookingPayload(Request $request): bool
    {
        return $request->get('espace') !== null
            && $request->get('dateReservation') !== null
            && $request->get('dateHeureDebut') !== null
            && $request->get('dateHeureFin') !== null;
    }

    private function handleQuickBooking(Request $request, EntityManagerInterface $entityManager): Response
    {
        $espaceId = (int) $request->get('espace');
        $objectif = trim((string) $request->get('objectif', ''));
        $dateReservation = \DateTime::createFromFormat('Y-m-d', (string) $request->get('dateReservation'));
        $dateHeureDebut = \DateTimeImmutable::createFromFormat('Y-m-d\TH:i', (string) $request->get('dateHeureDebut'));
        $dateHeureFin = \DateTimeImmutable::createFromFormat('Y-m-d\TH:i', (string) $request->get('dateHeureFin'));

        /** @var Espaces|null $espace */
        $espace = $entityManager->getRepository(Espaces::class)->find($espaceId);
        if (
            $espace === null
            || $dateReservation === false
            || $dateHeureDebut === false
            || $dateHeureFin === false
            || $dateHeureFin <= $dateHeureDebut
        ) {
            $this->addFlash('error', 'Données de réservation invalides.');
            return $this->redirectToRoute('app_reservation_espaces_index', [], Response::HTTP_SEE_OTHER);
        }

        if ($this->isCoworkingSpace($espace)) {
            $this->addFlash('error', 'Les espaces coworking se réservent chaise par chaise.');
            return $this->redirectToRoute('app_reservation_espaces_index', [], Response::HTTP_SEE_OTHER);
        }

        $startHour = (int) $dateHeureDebut->format('G');
        $startMinute = (int) $dateHeureDebut->format('i');
        $endHour = (int) $dateHeureFin->format('G');
        $endMinute = (int) $dateHeureFin->format('i');
        $durationSeconds = $dateHeureFin->getTimestamp() - $dateHeureDebut->getTimestamp();

        if (
            $objectif === ''
            || $startMinute !== 0
            || $endMinute !== 0
            || $startHour < 8
            || $endHour > 17
            || $durationSeconds !== 3600
            || $dateReservation->format('Y-m-d') !== $dateHeureDebut->format('Y-m-d')
            || $dateReservation->format('Y-m-d') !== $dateHeureFin->format('Y-m-d')
        ) {
            $this->addFlash('error', 'Choisissez un créneau de 1h entre 08:00 et 17:00 avec un objectif.');
            return $this->redirectToRoute('app_reservation_espaces_index', [], Response::HTTP_SEE_OTHER);
        }

        $conflictProbe = new Reservation_espaces();
        $conflictProbe->setIdEspace($espace);
        $conflictProbe->setDateReservation($dateReservation);
        $conflictProbe->setDateHeureDebut($dateHeureDebut);
        $conflictProbe->setDateHeureFin($dateHeureFin);
        if ($this->hasResourceTimeConflict($entityManager, $conflictProbe)) {
            $this->addFlash('error', 'This space is already reserved for the selected time slot');
            return $this->redirectToRoute('app_reservation_espaces_index', [], Response::HTTP_SEE_OTHER);
        }

        $reservation = new Reservation_espaces();
        $reservation->setIdEspace($espace);
        $reservation->setDateReservation($dateReservation);
        $reservation->setDateHeureDebut($dateHeureDebut);
        $reservation->setDateHeureFin($dateHeureFin);
        $reservation->setObjectif($objectif);
        $reservation->setStatut(true);
        $reservation->setCreeLe(new \DateTimeImmutable());
        $reservation->setIdEmploye($this->getAuthenticatedUserId());

        $entityManager->persist($reservation);
        $entityManager->flush();

        if ($request->headers->get('X-Requested-With') === 'XMLHttpRequest') {
            return $this->json(['success' => true, 'message' => 'Réservation créée avec succès.']);
        }

        $this->addFlash('success', 'Réservation créée avec succès.');
        return $this->redirectToRoute('app_reservation_espaces_index', [], Response::HTTP_SEE_OTHER);
    }

    private function denyAccessUnlessGrantedToReservation(Reservation_espaces $reservation): void
    {
        if ((int) $reservation->getIdEmploye() !== $this->getAuthenticatedUserId()) {
            throw $this->createAccessDeniedException('Vous ne pouvez accéder qu\'à vos réservations.');
        }
    }

    private function getAuthenticatedUserId(): int
    {
        $user = $this->getUser();
        if (!is_object($user) || !method_exists($user, 'getId')) {
            throw $this->createAccessDeniedException('Utilisateur non authentifié.');
        }

        return (int) $user->getId();
    }

    private function hasResourceTimeConflict(
        EntityManagerInterface $entityManager,
        Reservation_espaces $reservation,
        ?int $excludeReservationId = null
    ): bool {
        $espace = $reservation->getIdEspace();
        $start = $reservation->getDateHeureDebut();
        $end = $reservation->getDateHeureFin();

        if ($espace === null || $start === null || $end === null) {
            return false;
        }

        $qb = $entityManager->getRepository(Reservation_espaces::class)->createQueryBuilder('r');
        $qb
            ->select('COUNT(r.id)')
            ->andWhere('r.idEspace = :espace')
            ->andWhere('r.dateHeureDebut < :newEnd')
            ->andWhere('r.dateHeureFin > :newStart')
            ->setParameter('espace', $espace)
            ->setParameter('newStart', $start)
            ->setParameter('newEnd', $end);

        if ($excludeReservationId !== null) {
            $qb->andWhere('r.id != :excludeId')->setParameter('excludeId', $excludeReservationId);
        }

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }

    private function isCoworkingSpace(Espaces $espace): bool
    {
        return str_contains($this->normalizeType((string) $espace->getTypeEspace()), 'cowork');
    }

    private function normalizeType(string $type): string
    {
        return strtolower(str_replace([' ', '-', '_'], '', trim($type)));
    }
}
