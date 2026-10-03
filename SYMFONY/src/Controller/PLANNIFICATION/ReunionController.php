<?php

namespace App\Controller\PLANNIFICATION;

use App\Entity\Espaces;
use App\Entity\Reunion;
use App\Form\PLANNIFICATION\ReunionModalType;
use App\Service\Plannification\ReunionCalendarMapper;
use App\Service\Plannification\ReunionZoomIntegration;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/reunion')]
final class ReunionController extends AbstractController
{
    use AjaxFormResponseTrait;

    public function __construct(
        private readonly ReunionZoomIntegration $reunionZoom,
    ) {}

    // -------------------------------------------------------------------------
    // LIST
    // -------------------------------------------------------------------------
    #[Route(name: 'app_reunion_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $reunions = $entityManager->getRepository(Reunion::class)->findBy([
            'emailOrganisateur' => $this->getAuthenticatedUserEmail(),
        ]);
        $salles = $entityManager->getRepository(Espaces::class)->findAll();

        $create = new Reunion();
        $this->initReunionDefaults($create);

        $formCreate = $this->createForm(ReunionModalType::class, $create, [
            'action' => $this->generateUrl('app_reunion_new'),
            'method' => 'POST',
        ]);

        return $this->render('PLANNIFICATION/reunion/index.html.twig', [
            'reunions'           => $reunions,
            'salles'             => $salles,
            'form_create'        => $formCreate->createView(),
            'open_reunion_modal' => false,
        ]);
    }

    // -------------------------------------------------------------------------
    // CALENDAR VIEW
    // -------------------------------------------------------------------------
    #[Route('/calendar', name: 'app_reunion_calendar', methods: ['GET'])]
    public function calendar(): Response
    {
        $create = new Reunion();
        $this->initReunionDefaults($create);

        $formCreate = $this->createForm(ReunionModalType::class, $create, [
            'action' => $this->generateUrl('app_reunion_new'),
            'method' => 'POST',
        ]);

        return $this->render('PLANNIFICATION/reunion/calendar.html.twig', [
            'form_create'        => $formCreate->createView(),
            'open_reunion_modal' => false,
        ]);
    }

    // -------------------------------------------------------------------------
    // API JSON pour FullCalendar  (filtrée par utilisateur connecté)
    // -------------------------------------------------------------------------
    #[Route('/api/reunions', name: 'api_reunions', methods: ['GET'])]
    public function apiReunions(EntityManagerInterface $entityManager): JsonResponse
    {
        // On filtre par organisateur pour n'exposer que les réunions de l'utilisateur connecté.
        $reunions = $entityManager->getRepository(Reunion::class)->findBy([
            'emailOrganisateur' => $this->getAuthenticatedUserEmail(),
        ]);

        $events = [];
        foreach ($reunions as $reunion) {
            $color = ReunionCalendarMapper::eventColor($reunion);

            $events[] = [
                'id'              => $reunion->getId(),
                'title'           => $reunion->getTitre(),
                'start'           => $reunion->getDateHeureDebut()?->format('Y-m-d\TH:i:s'),
                'end'             => $reunion->getDateHeureFin()?->format('Y-m-d\TH:i:s'),
                'backgroundColor' => $color,
                'borderColor'     => $color,
                'url'             => $this->generateUrl('app_reunion_show', ['id' => $reunion->getId()]),
                'extendedProps'   => [
                    ...ReunionCalendarMapper::extendedProps($reunion),
                    // CSRF token pour la suppression depuis le popover du calendrier
                    'csrfToken'   => $this->container->get('security.csrf.token_manager')
                                         ->getToken('delete' . $reunion->getId())
                                         ->getValue(),
                ],
            ];
        }

        return new JsonResponse($events);
    }

    // -------------------------------------------------------------------------
    // CREATE
    // -------------------------------------------------------------------------
    #[Route('/new', name: 'app_reunion_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('GET')) {
            return $this->redirectToRoute('app_reunion_index', [], Response::HTTP_SEE_OTHER);
        }

        $reunion = new Reunion();
        $this->initReunionDefaults($reunion);

        $form = $this->createForm(ReunionModalType::class, $reunion, [
            'action' => $this->generateUrl('app_reunion_new'),
            'method' => 'POST',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            ReunionModalType::applyDateTimesAndBusinessRules($form, $reunion);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->hydrateOrganizerFromAuthenticatedUser($reunion);
            $reunion->setCreeLe(new \DateTimeImmutable());
            $zoomOutcome = $this->reunionZoom->syncNewReunion($reunion);

            $entityManager->persist($reunion);
            $entityManager->flush();

            if ($this->wantsModalJson($request)) {
                return $this->jsonModalSuccess('app_reunion_index', [], [], $zoomOutcome->toJsonFragment());
            }

            return $this->redirectToRoute('app_reunion_index', [], Response::HTTP_SEE_OTHER);
        }

        if ($form->isSubmitted() && !$form->isValid() && $this->wantsModalJson($request)) {
            return $this->jsonModalInvalid($form);
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $reunions = $entityManager->getRepository(Reunion::class)->findBy([
                'emailOrganisateur' => $this->getAuthenticatedUserEmail(),
            ]);
            $salles = $entityManager->getRepository(Espaces::class)->findAll();

            return $this->render('PLANNIFICATION/reunion/index.html.twig', [
                'reunions'           => $reunions,
                'salles'             => $salles,
                'form_create'        => $form->createView(),
                'open_reunion_modal' => true,
            ]);
        }

        return $this->redirectToRoute('app_reunion_index', [], Response::HTTP_SEE_OTHER);
    }

    // -------------------------------------------------------------------------
    // SHOW  (accessible à tous les utilisateurs authentifiés — pas de filtre owner
    //        car le lien est partageable depuis le calendrier)
    // -------------------------------------------------------------------------
    #[Route('/{id}', name: 'app_reunion_show', methods: ['GET'])]
    public function show(Reunion $reunion): Response
    {
        return $this->render('PLANNIFICATION/reunion/show.html.twig', [
            'reunion' => $reunion,
        ]);
    }

    // -------------------------------------------------------------------------
    // EDIT
    // -------------------------------------------------------------------------
    #[Route('/{id}/edit', name: 'app_reunion_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reunion $reunion, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessOwner($reunion);

        $wasOnline    = $reunion->getEnLigne();
        $oldMeetingId = $reunion->getZoom_meeting_id();

        $form = $this->createForm(ReunionModalType::class, $reunion, [
            'action' => $this->generateUrl('app_reunion_edit', ['id' => $reunion->getId()]),
            'method' => 'POST',
        ]);
        $form->handleRequest($request);

        // ── GET ─────────────────────────────────────────────────────────────
        if ($request->isMethod('GET')) {
            if ($this->isXmlHttpRequest($request)) {
                $html = $this->renderView('PLANNIFICATION/reunion/_modal_edit_reunion.html.twig', [
                    'form'    => $form->createView(),
                    'reunion' => $reunion,
                ]);
                return new JsonResponse(['formHtml' => $html]);
            }

            return $this->render('PLANNIFICATION/reunion/edit.html.twig', [
                'reunion' => $reunion,
                'form'    => $form->createView(),
            ]);
        }

        // ── POST ─────────────────────────────────────────────────────────────
        if ($form->isSubmitted()) {
            ReunionModalType::applyDateTimesAndBusinessRules($form, $reunion);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->hydrateOrganizerFromAuthenticatedUser($reunion);
            $this->reunionZoom->syncEditedReunion($reunion, $wasOnline, $oldMeetingId);
            $entityManager->flush();

            if ($this->isXmlHttpRequest($request)) {
                return new JsonResponse([
                    'success'  => true,
                    'redirect' => $this->generateUrl('app_reunion_index'),
                ]);
            }

            return $this->redirectToRoute('app_reunion_index', [], Response::HTTP_SEE_OTHER);
        }

        // ── POST invalide ────────────────────────────────────────────────────
        if ($this->isXmlHttpRequest($request)) {
            $html = $this->renderView('PLANNIFICATION/reunion/_modal_edit_reunion.html.twig', [
                'form'    => $form->createView(),
                'reunion' => $reunion,
            ]);
            return new JsonResponse(['success' => false, 'formHtml' => $html]);
        }

        return $this->render('PLANNIFICATION/reunion/edit.html.twig', [
            'reunion' => $reunion,
            'form'    => $form->createView(),
        ]);
    }

    // -------------------------------------------------------------------------
    // DELETE
    // -------------------------------------------------------------------------
    #[Route('/{id}', name: 'app_reunion_delete', methods: ['POST'])]
    public function delete(Request $request, Reunion $reunion, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessOwner($reunion);

        if ($this->isCsrfTokenValid('delete' . $reunion->getId(), $request->getPayload()->getString('_token'))) {
            $this->reunionZoom->deleteZoomMeetingIfAny($reunion);
            $entityManager->remove($reunion);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_reunion_index', [], Response::HTTP_SEE_OTHER);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------
    private function isXmlHttpRequest(Request $request): bool
    {
        return $request->headers->get('X-Requested-With') === 'XMLHttpRequest';
    }

    private function initReunionDefaults(Reunion $r): void
    {
        $user           = $this->getUser();
        $organizerName  = is_object($user) && method_exists($user, 'getUsername')
            ? (string) $user->getUsername()
            : 'Utilisateur';
        $organizerEmail = is_object($user) && method_exists($user, 'getEmail')
            ? (string) $user->getEmail()
            : 'utilisateur@humania.local';

        $r->setTitre('');
        $r->setDescription('');
        $r->setNomOrganisateur($organizerName);
        $r->setEmailOrganisateur($organizerEmail);
        $r->setParticipants('');
        $r->setStatut(true);
        $r->setEnLigne(false);
        $r->setIdSalle(null);

        $today = new \DateTimeImmutable('today');
        $r->setDateHeureDebut($today->setTime(9, 0));
        $r->setDateHeureFin($today->setTime(10, 0));

        $r->setCreeLe(new \DateTimeImmutable());
        $r->setZoom_meeting_id('0');
        $r->setZoom_join_url('');
        $r->setZoom_start_url('');
        $r->setZoom_password('');
    }

    private function denyAccessUnlessOwner(Reunion $reunion): void
    {
        if ($reunion->getEmailOrganisateur() !== $this->getAuthenticatedUserEmail()) {
            throw $this->createAccessDeniedException('Vous ne pouvez modifier/supprimer que vos réunions.');
        }
    }

    private function hydrateOrganizerFromAuthenticatedUser(Reunion $reunion): void
    {
        $user = $this->getUser();
        $name = is_object($user) && method_exists($user, 'getUsername')
            ? (string) $user->getUsername()
            : 'Utilisateur';

        $reunion->setNomOrganisateur($name);
        $reunion->setEmailOrganisateur($this->getAuthenticatedUserEmail());
    }

    private function getAuthenticatedUserEmail(): string
    {
        $user = $this->getUser();
        if (!is_object($user) || !method_exists($user, 'getEmail')) {
            throw $this->createAccessDeniedException('Utilisateur non authentifié.');
        }

        return (string) $user->getEmail();
    }
}
