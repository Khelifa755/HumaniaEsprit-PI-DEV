<?php

namespace App\Controller\PLANNIFICATION;

use App\Entity\Espaces;
use App\Service\Plannification\CoworkingReservationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Java equivalent: CoworkingController / ReservEspaceCont (JavaFX controllers)
 *
 * Handles the full coworking seat-reservation flow:
 *   1. Show available coworking espaces (replaces the FXML espace-picker scene)
 *   2. Show seat grid for a chosen espace + date
 *   3. Reserve a single chair (POST)
 *   4. Cancel a chair (POST)
 *   5. Cancel all chairs for the current user (POST)
 *
 * Route prefix mirrors the existing PHP controller namespace convention.
 */
#[Route('/coworking')]
#[IsGranted('ROLE_USER')]
class CoworkingReservationController extends AbstractController
{
    public function __construct(
        private readonly CoworkingReservationService $reservationService,
        private readonly EntityManagerInterface $em
    ) {}

    // ────────────────────────────────────────────────────────────────────────
    // 1. Espace list
    //    Java: the JavaFX scene that listed Espace objects of type "coworking"
    // ────────────────────────────────────────────────────────────────────────

    #[Route('', name: 'coworking_index', methods: ['GET'])]
    public function index(): Response
    {
        // Equivalent to the Java EspaceService query filtered by typeEspace = "coworking"
        $espaces = $this->em->getRepository(Espaces::class)
            ->findBy(['typeEspace' => 'coworking', 'disponible' => true]);

        return $this->render('PLANNIFICATION/coworking_reservation/index.html.twig', [
            'espaces' => $espaces,
        ]);
    }

    // ────────────────────────────────────────────────────────────────────────
    // 2. Seat grid for one espace + date
    //    Java: CoworkingController.initialize() + loadReservations() +
    //          buildChairGrid() — the heart of the JavaFX controller
    // ────────────────────────────────────────────────────────────────────────

    #[Route('/{id}/seats', name: 'coworking_seats', methods: ['GET'])]
    public function seats(int $id, Request $request): Response
    {
        $espace = $this->findCoworkingEspaceOr404($id);

        // Java: DatePicker value — defaulting to today when not provided
        $dateParam = $request->query->get('date', date('Y-m-d'));
        $day = \DateTimeImmutable::createFromFormat('Y-m-d', $dateParam);
        if ($day === false) {
            throw $this->createNotFoundException('Date invalide.');
        }

        // Java: service.getReservationsForDate(espaceId, day)
        $reservations = $this->reservationService->getReservationsForDate($id, $day);

        // Java: buildChairGrid() in the controller — now in the service
        $chairs = $this->reservationService->buildChairGrid(
            $espace->getCapacite(),
            $reservations,
            $this->getCurrentUserId()
        );

        return $this->render('PLANNIFICATION/coworking_reservation/seats.html.twig', [
            'espace'     => $espace,
            'chairs'     => $chairs,
            'date'       => $day->format('Y-m-d'),
            'dateDisplay'=> $day->format('d/m/Y'),
        ]);
    }

    #[Route('/{id}/availability', name: 'coworking_availability', methods: ['GET'])]
    public function availability(int $id, Request $request): JsonResponse
    {
        $espace = $this->findCoworkingEspaceOr404($id);
        $dateParam = (string) $request->query->get('date', date('Y-m-d'));
        $day = \DateTimeImmutable::createFromFormat('Y-m-d', $dateParam);

        if ($day === false) {
            return $this->json(['message' => 'Date invalide.'], Response::HTTP_BAD_REQUEST);
        }

        $reservations = $this->reservationService->getReservationsForDate($id, $day);
        $chairs = $this->reservationService->buildChairGrid(
            $espace->getCapacite(),
            $reservations,
            $this->getCurrentUserId()
        );

        return $this->json([
            'date' => $day->format('Y-m-d'),
            'chairs' => array_map(static fn ($chair) => [
                'seatNumber' => $chair->getSeatNumber(),
                'state' => $chair->getState(),
                'reservedByUserId' => $chair->getReservedByUserId(),
            ], $chairs),
        ]);
    }

    // ────────────────────────────────────────────────────────────────────────
    // 3. Reserve a single chair (POST)
    //    Java: CoworkingController.onChairClick() → service.reserveChair()
    // ────────────────────────────────────────────────────────────────────────

    #[Route('/{id}/reserve', name: 'coworking_reserve', methods: ['POST'])]
    public function reserve(int $id, Request $request): Response
    {
        $chairNumber = (int) $request->request->get('chair_number');
        $token = (string) $request->request->get('_token');
        if (
            !$this->isCsrfTokenValid('reserve_chair_' . $chairNumber, $token)
            && !$this->isCsrfTokenValid('reserve_chair_popup', $token)
        ) {
            $this->addFlash('error', 'Token de sécurité invalide.');
            return $this->redirectToRoute('coworking_seats', ['id' => $id]);
        }

        $espace      = $this->findCoworkingEspaceOr404($id);
        $dateParam   = $request->request->get('date', date('Y-m-d'));
        $day         = \DateTimeImmutable::createFromFormat('Y-m-d', $dateParam);

        if ($day === false || $chairNumber < 1 || $chairNumber > $espace->getCapacite()) {
            $this->addFlash('error', 'Requête invalide.');
            return $this->redirectToRoute('coworking_seats', ['id' => $id, 'date' => $dateParam]);
        }

        // Java: service.reserveChair(espaceId, chairNumber, userId, day)
        // Same validation: throws RuntimeException → IllegalStateException if taken
        try {
            $this->reservationService->reserveChair(
                $id,
                $chairNumber,
                $this->getCurrentUserId(),
                $day
            );
            $this->addFlash('success', sprintf('Chaise %d réservée avec succès.', $chairNumber));
        } catch (\RuntimeException $e) {
            // Java: the RuntimeException message from the lock check is shown to the user
            $this->addFlash('error', $e->getPrevious()?->getMessage() ?? $e->getMessage());
        }

        return $this->redirectToRoute('coworking_seats', ['id' => $id, 'date' => $dateParam]);
    }

    // ────────────────────────────────────────────────────────────────────────
    // 4. Cancel a single chair (POST)
    //    Java: service.cancelChair()
    // ────────────────────────────────────────────────────────────────────────

    #[Route('/{id}/cancel-chair', name: 'coworking_cancel_chair', methods: ['POST'])]
    public function cancelChair(int $id, Request $request): Response
    {
        $chairNumber = (int) $request->request->get('chair_number');
        $token = (string) $request->request->get('_token');
        if (
            !$this->isCsrfTokenValid('cancel_chair_' . $chairNumber, $token)
            && !$this->isCsrfTokenValid('cancel_chair_popup', $token)
        ) {
            $this->addFlash('error', 'Token de sécurité invalide.');
            return $this->redirectToRoute('coworking_seats', ['id' => $id]);
        }

        $espace      = $this->findCoworkingEspaceOr404($id);
        $dateParam   = $request->request->get('date', date('Y-m-d'));
        $day         = \DateTimeImmutable::createFromFormat('Y-m-d', $dateParam);

        if ($day === false || $chairNumber < 1) {
            $this->addFlash('error', 'Requête invalide.');
            return $this->redirectToRoute('coworking_seats', ['id' => $id, 'date' => $dateParam]);
        }

        try {
            // Java: service.cancelChair(espaceId, chairNumber, userId, day)
            // userId is enforced so users can only cancel their OWN reservations
            $this->reservationService->cancelChair(
                $id,
                $chairNumber,
                $this->getCurrentUserId(),
                $day
            );
            $this->addFlash('success', sprintf('Réservation de la chaise %d annulée.', $chairNumber));
        } catch (\RuntimeException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('coworking_seats', ['id' => $id, 'date' => $dateParam]);
    }

    // ────────────────────────────────────────────────────────────────────────
    // 5. Cancel all chairs for current user (POST)
    //    Java: service.cancelAllForUser()
    // ────────────────────────────────────────────────────────────────────────

    #[Route('/{id}/cancel-all', name: 'coworking_cancel_all', methods: ['POST'])]
    public function cancelAll(int $id, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('cancel_all', (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Token de sécurité invalide.');
            return $this->redirectToRoute('coworking_seats', ['id' => $id]);
        }

        $this->findCoworkingEspaceOr404($id);
        $dateParam = $request->request->get('date', date('Y-m-d'));
        $day       = \DateTimeImmutable::createFromFormat('Y-m-d', $dateParam);

        if ($day === false) {
            $this->addFlash('error', 'Date invalide.');
            return $this->redirectToRoute('coworking_seats', ['id' => $id, 'date' => $dateParam]);
        }

        try {
            // Java: service.cancelAllForUser(espaceId, userId, day)
            $this->reservationService->cancelAllForUser(
                $id,
                $this->getCurrentUserId(),
                $day
            );
            $this->addFlash('success', 'Toutes vos réservations du jour ont été annulées.');
        } catch (\RuntimeException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('coworking_seats', ['id' => $id, 'date' => $dateParam]);
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /**
     * Loads the Espaces entity and verifies it is of type "coworking".
     * Java: Espace.isCoworking() — enforced before every action.
     */
    private function findCoworkingEspaceOr404(int $id): Espaces
    {
        /** @var Espaces|null $espace */
        $espace = $this->em->getRepository(Espaces::class)->find($id);

        if ($espace === null) {
            throw $this->createNotFoundException('Espace introuvable.');
        }

        // Java: Espace.isCoworking() → "coworking".equalsIgnoreCase(typeEspace.trim())
        if (strtolower(trim($espace->getTypeEspace())) !== 'coworking') {
            throw $this->createNotFoundException('Cet espace n\'est pas de type coworking.');
        }

        return $espace;
    }

    /**
     * Returns the integer user id of the currently authenticated user.
     * Replaces the Java field `private int currentUserId` injected into controllers.
     */
    private function getCurrentUserId(): int
    {
        $user = $this->getUser();
        if (!is_object($user) || !method_exists($user, 'getId')) {
            throw $this->createAccessDeniedException('Utilisateur non authentifié.');
        }

        return (int) $user->getId();
    }
}