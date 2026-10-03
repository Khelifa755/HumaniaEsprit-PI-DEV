<?php

namespace App\Controller\PLANNIFICATION;

use App\Entity\Evenement;
use App\Entity\Participation_evenement;
use App\Repository\PLANIFICATION\ParticipationEvenementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\SvgWriter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/part-evenement')]
#[IsGranted('ROLE_USER')]
final class ParticipationEvenementController extends AbstractController
{
    // ── List ─────────────────────────────────────────────────────────────────

    #[Route('', name: 'app_part_evenement_index', methods: ['GET'])]
    public function index(
        EntityManagerInterface $em,
        ParticipationEvenementRepository $repo,
    ): Response {
        $idEmploye = $this->currentUserEmployeId();
        $now       = new \DateTimeImmutable();

        $allEvents = $em->getRepository(Evenement::class)->createQueryBuilder('e')
            ->where('e.dateHeureDebut > :now')
            ->setParameter('now', $now)
            ->orderBy('e.dateHeureDebut', 'ASC')
            ->getQuery()
            ->getResult();

        $myParticipations = $repo->findActiveByEmploye($idEmploye);
        $joinedIds        = [];
        foreach ($myParticipations as $p) {
            $joinedIds[$p->getIdEvenement()->getId()] = $p;
        }

        $eventIds      = array_map(fn(Evenement $e) => $e->getId(), $allEvents);
        $countsByEvent = $repo->countsByEventIds($eventIds);

        return $this->render('PLANNIFICATION/part_evenement/index.html.twig', [
            'evenements'    => $allEvents,
            'joinedIds'     => $joinedIds,
            'countsByEvent' => $countsByEvent,
            'idEmploye'     => $idEmploye,
        ]);
    }

    // ── Rejoindre ─────────────────────────────────────────────────────────────

    #[Route('/{id}/join', name: 'app_part_evenement_join', methods: ['POST'])]
    public function join(
        Evenement $evenement,
        Request $request,
        EntityManagerInterface $em,
        ParticipationEvenementRepository $repo,
    ): Response {
        if (!$this->isCsrfTokenValid('join_event_' . $evenement->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token de sécurité invalide. Veuillez réessayer.');
            return $this->redirectToRoute('app_part_evenement_index');
        }

        $idEmploye = $this->currentUserEmployeId();

        if ($evenement->getDateHeureDebut() <= new \DateTimeImmutable()) {
            $this->addFlash('error', 'Vous ne pouvez participer qu\'à des événements à venir.');
            return $this->redirectToRoute('app_part_evenement_index');
        }

        if ($repo->findOneActiveByEmployeAndEvent($idEmploye, $evenement)) {
            $this->addFlash('error', 'Vous participez déjà à cet événement.');
            return $this->redirectToRoute('app_part_evenement_index');
        }

        $confirmed = $repo->countConfirmedByEvent($evenement);
        if ($evenement->getNbParticipantsMax() > 0 && $confirmed >= $evenement->getNbParticipantsMax()) {
            $this->addFlash('error', 'Cet événement est complet (capacité maximale atteinte).');
            return $this->redirectToRoute('app_part_evenement_index');
        }

        $p = new Participation_evenement();
        $p->setIdEvenement($evenement);
        $p->setIdEmploye($idEmploye);
        $p->setDateParticipation(new \DateTime());
        $p->setStatut('confirme');
        $p->setCreeLe(new \DateTime());

        $em->persist($p);
        $em->flush();

        $this->addFlash('success', 'Participation confirmée pour « ' . $evenement->getTitre() . ' ».');
        return $this->redirectToRoute('app_part_evenement_index');
    }

    // ── Annuler ───────────────────────────────────────────────────────────────

    #[Route('/{id}/cancel', name: 'app_part_evenement_cancel', methods: ['POST'])]
    public function cancel(
        Evenement $evenement,
        Request $request,
        EntityManagerInterface $em,
        ParticipationEvenementRepository $repo,
    ): Response {
        if (!$this->isCsrfTokenValid('cancel_event_' . $evenement->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token de sécurité invalide. Veuillez réessayer.');
            return $this->redirectToRoute('app_part_evenement_index');
        }

        $idEmploye = $this->currentUserEmployeId();

        $p = $repo->findOneActiveByEmployeAndEvent($idEmploye, $evenement);
        if (!$p) {
            $this->addFlash('error', 'Participation introuvable.');
            return $this->redirectToRoute('app_part_evenement_index');
        }

        if ($evenement->getDateHeureDebut() <= new \DateTimeImmutable()) {
            $this->addFlash('error', 'Vous ne pouvez plus annuler la participation à un événement passé ou en cours.');
            return $this->redirectToRoute('app_part_evenement_index');
        }

        $p->setStatut('annule');
        $em->flush();

        $this->addFlash('success', 'Participation annulée pour « ' . $evenement->getTitre() . ' ».');
        return $this->redirectToRoute('app_part_evenement_index');
    }

    // ── Badge (QR Code) ───────────────────────────────────────────────────────

    #[Route('/{id}/badge', name: 'app_part_evenement_badge', methods: ['GET'])]
    public function badge(
        Evenement $evenement,
        ParticipationEvenementRepository $repo,
    ): Response {
        $idEmploye = $this->currentUserEmployeId();

        $participation = $repo->findOneActiveByEmployeAndEvent($idEmploye, $evenement);
        if (!$participation) {
            $this->addFlash('error', 'Vous n\'avez pas de participation active pour cet événement.');
            return $this->redirectToRoute('app_part_evenement_index');
        }

        $employee = $this->getAuthenticatedEmployeeProfile();
        $code     = $this->buildVerificationCode($employee, $evenement, $participation);

        /*
         * Contenu du QR code : texte lisible par n'importe quel scanner.
         * Chaque ligne est un champ clairement labellisé.
         * Le code de vérification (HMAC court) permet de valider l'authenticité
         * sans exposer de données sensibles.
         *
         * Exemple de rendu scanné :
         *   BADGE HUMANIA
         *   ─────────────────────────
         *   Nom     : Dupont Jean
         *   Ref     : EMP-0042
         *   ─────────────────────────
         *   Événement : Journée RH 2025
         *   Lieu      : Salle Agora
         *   Date      : 15/06/2025
         *   Horaire   : 09:00 → 17:00
         *   ─────────────────────────
         *   Statut    : CONFIRMÉ
         *   Inscrit le: 01/06/2025
         *   ─────────────────────────
         *   Code      : A3F9C2E1B047D85F
         */
        $sep     = str_repeat('-', 33);
        $payload = implode("\n", [
            'BADGE HUMANIA',
            $sep,
            'Nom     : ' . $employee['lastName'] . ' ' . $employee['firstName'],
            'Ref     : ' . $employee['reference'],
            $sep,
            'Evenement : ' . $evenement->getTitre(),
            'Lieu      : ' . ($evenement->getLieu() ?? 'N/A'),
            'Date      : ' . ($evenement->getDateEvenement()?->format('d/m/Y') ?? 'N/A'),
            'Horaire   : ' . ($evenement->getDateHeureDebut()?->format('H:i') ?? '--:--')
                           . ' -> '
                           . ($evenement->getDateHeureFin()?->format('H:i') ?? '--:--'),
            $sep,
            'Statut    : ' . strtoupper($participation->getStatut()),
            'Inscrit le: ' . $participation->getDateParticipation()->format('d/m/Y'),
            $sep,
            'Code      : ' . $code,
        ]);

        // SVG writer : pas besoin de l'extension GD
        $qr = (new Builder())->build(
            writer: new SvgWriter(),
            data: $payload,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::Medium, // M = bon compromis taille/robustesse
            size: 260,
            margin: 10,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
        );

        return $this->render('PLANNIFICATION/part_evenement/badge.html.twig', [
            'evenement'     => $evenement,
            'participation' => $participation,
            'qrDataUri'     => 'data:image/svg+xml;base64,' . base64_encode($qr->getString()),
            'employee'      => $employee,
            'verifyCode'    => $code,
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function currentUserEmployeId(): int
    {
        $user = $this->getUser();
        if (!is_object($user) || !method_exists($user, 'getId')) {
            throw $this->createAccessDeniedException('Utilisateur non authentifié.');
        }

        return (int) $user->getId();
    }

    /**
     * @return array{firstName:string, lastName:string, reference:string}
     */
    private function getAuthenticatedEmployeeProfile(): array
    {
        $user = $this->getUser();
        if (!is_object($user)) {
            throw $this->createAccessDeniedException('Utilisateur non authentifié.');
        }

        $firstName = method_exists($user, 'getPrenom')    ? (string) ($user->getPrenom()    ?? '') : '';
        $lastName  = method_exists($user, 'getNom')       ? (string) ($user->getNom()       ?? '') : '';

        if ($firstName === '' && method_exists($user, 'getFirstName')) {
            $firstName = (string) ($user->getFirstName() ?? '');
        }
        if ($lastName === '' && method_exists($user, 'getLastName')) {
            $lastName = (string) ($user->getLastName() ?? '');
        }

        $reference = method_exists($user, 'getMatricule')
            ? (string) ($user->getMatricule() ?? '')
            : '';

        if ($reference === '' && method_exists($user, 'getUsername')) {
            $reference = (string) ($user->getUsername() ?? '');
        }

        return [
            'firstName' => $firstName !== '' ? $firstName : 'Employé',
            'lastName'  => $lastName  !== '' ? $lastName  : 'Humania',
            'reference' => $reference !== '' ? $reference : 'N/A',
        ];
    }

    /**
     * HMAC court (24 hex) utilisé comme code de vérification sur le badge.
     * Ne contient aucun identifiant interne brut.
     *
     * @param array{firstName:string, lastName:string, reference:string} $employee
     */
    private function buildVerificationCode(
        array $employee,
        Evenement $evenement,
        Participation_evenement $participation,
    ): string {
        $input = implode('|', [
            $employee['reference'],
            $employee['firstName'],
            $employee['lastName'],
            $evenement->getTitre(),
            $evenement->getDateHeureDebut()?->format(\DateTimeInterface::ATOM),
            $participation->getDateParticipation()->format('Y-m-d'),
            $participation->getStatut(),
        ]);

        $secret = (string) ($_ENV['APP_SECRET'] ?? 'humania-badge');

        return strtoupper(substr(hash_hmac('sha256', $input, $secret), 0, 16));
    }
}