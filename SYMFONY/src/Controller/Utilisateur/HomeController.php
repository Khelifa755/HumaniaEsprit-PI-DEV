<?php

namespace App\Controller\Utilisateur;

use App\Entity\Utilisateur;
use App\Service\Utilisateur\UtilisateurService;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class HomeController extends AbstractController
{
    public function __construct(
        private readonly UtilisateurService     $utilisateurService,
        private readonly EntityManagerInterface $em,
    ) {}

    #[Route('/home', name: 'app_home')]
    public function index(): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        /** @var Utilisateur $user */
        $user = $this->getUser();

        return match ($user->getRole()?->value) {
            'EMPLOYE'
            => $this->render('Utilisateur/dashboard.html.twig', $this->getEmployeData($user)),
            default
            => $this->render('Utilisateur/dashboard.html.twig', $this->getDashboardData()),
        };
    }

    // ── Dashboard Admin / RH / Manager / Formateur ───────────────────────────

    private function getDashboardData(): array
    {
        $user  = $this->getUser();
        $stats = $this->utilisateurService->getStats(
            $user instanceof Utilisateur ? $user : null
        );

        $conn        = $this->em->getConnection();
        $monthlyData = $conn->fetchAllAssociative(
            "SELECT DATE_FORMAT(date_depot, '%Y-%m') AS mois,
             CAST(COUNT(*) AS UNSIGNED) AS total
             FROM candidature
             WHERE date_depot IS NOT NULL
             GROUP BY DATE_FORMAT(date_depot, '%Y-%m')
             ORDER BY mois ASC
             LIMIT 12"
        );

        $months = [];
        $counts = [];
        foreach ($monthlyData as $row) {
            $months[] = $row['mois'];
            $counts[] = (int) $row['total'];
        }

        return [
            'stats'       => $stats,
            'chartMonths' => json_encode($months),
            'chartCounts' => json_encode($counts),
        ];
    }

    // ── Dashboard Employé — stats personnelles ────────────────────────────────
    //
    // Colonnes confirmées via DESCRIBE :
    //
    // conge            → utilisateur_id, statut, date_debut, date_fin
    // absence          → utilisateur_id, statut, date_debut, date_fin
    // inscriptionformation → employe_id, statut, session_id
    // resultatevaluation   → employe_id, note (score), evaluation_id
    // competenceemploye    → employe_id, competence_id, niveauValide, niveauActuel
    // notification         → utilisateur_id
    // publication          → (pas de FK user directe — on compte tout)

    private function getEmployeData(Utilisateur $user): array
    {
        $conn = $this->em->getConnection();
        $id   = $user->getId();

        // ── Congés (conge.utilisateur_id) ─────────────────────────────────────
        $mesConges          = $this->q($conn, "SELECT COUNT(*) FROM conge WHERE utilisateur_id = ?", [$id]);
        $mesCongesEnAttente = $this->q($conn, "SELECT COUNT(*) FROM conge WHERE utilisateur_id = ? AND statut = 'En attente'", [$id]);
        $mesCongesApprouves = $this->q($conn, "SELECT COUNT(*) FROM conge WHERE utilisateur_id = ? AND statut = 'Approuve'", [$id]);

        // ── Absences (absence.utilisateur_id) ────────────────────────────────
        $mesAbsences = $this->q($conn,
            "SELECT COUNT(*) FROM absence
             WHERE utilisateur_id = ?
             AND MONTH(date_debut) = MONTH(CURDATE())
             AND YEAR(date_debut)  = YEAR(CURDATE())",
            [$id]
        );

        // ── Formations (inscriptionformation.employe_id) ─────────────────────
        $mesFormations        = $this->q($conn, "SELECT COUNT(*) FROM inscriptionformation WHERE employe_id = ?", [$id]);
        $mesFormationsEnCours = $this->q($conn, "SELECT COUNT(*) FROM inscriptionformation WHERE employe_id = ? AND statut = 'In Progress'", [$id]);

        // ── Évaluations (resultatevaluation.employe_id, colonne: note) ────────
        $mesEvaluations = $this->q($conn, "SELECT COUNT(*) FROM resultatevaluation WHERE employe_id = ?", [$id]);
        $monScoreEval   = $this->val($conn, "SELECT ROUND(AVG(note), 0) FROM resultatevaluation WHERE employe_id = ?", [$id], '—');

        // ── Compétences (competenceemploye.employe_id) ────────────────────────
        // niveauValide = 1 → compétence validée
        $mesCompetences         = $this->q($conn, "SELECT COUNT(*) FROM competenceemploye WHERE employe_id = ?", [$id]);
        $mesCompetencesValidees = $this->q($conn, "SELECT COUNT(*) FROM competenceemploye WHERE employe_id = ? AND niveauValide >= 1", [$id]);

        // Répartition par type via JOIN competence
        $mesCompetencesByType = [];
        try {
            $rows = $conn->fetchAllAssociative(
                "SELECT c.criticite AS type, COUNT(*) AS cnt
                 FROM competenceemploye ce
                 JOIN competence c ON c.id = ce.competence_id
                 WHERE ce.employe_id = ?
                 GROUP BY c.criticite",
                [$id]
            );
            foreach ($rows as $r) {
                $mesCompetencesByType[$r['type']] = (int) $r['cnt'];
            }
        } catch (\Throwable) {}

        // ── Social ────────────────────────────────────────────────────────────
        $totalPublications  = $this->q($conn, "SELECT COUNT(*) FROM publication", []);
        $totalNotifications = $this->q($conn, "SELECT COUNT(*) FROM notification WHERE utilisateur_id = ?", [$id]);

        return [
            'stats' => [
                'mesConges'              => $mesConges,
                'mesCongesEnAttente'     => $mesCongesEnAttente,
                'mesCongesApprouves'     => $mesCongesApprouves,
                'mesAbsences'            => $mesAbsences,
                'mesFormations'          => $mesFormations,
                'mesFormationsEnCours'   => $mesFormationsEnCours,
                'mesEvaluations'         => $mesEvaluations,
                'monScoreEval'           => $monScoreEval,
                'mesCompetences'         => $mesCompetences,
                'mesCompetencesValidees' => $mesCompetencesValidees,
                'mesCompetencesByType'   => $mesCompetencesByType,
                'totalPublications'      => $totalPublications,
                'totalMessages'          => 0,
                'totalEvenements'        => 0,
                'totalNotifications'     => $totalNotifications,
            ],
            'chartMonths' => json_encode([]),
            'chartCounts' => json_encode([]),
        ];
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function q(Connection $conn, string $sql, array $p): int
    {
        try { return (int) $conn->fetchOne($sql, $p); }
        catch (\Throwable) { return 0; }
    }

    private function val(Connection $conn, string $sql, array $p, mixed $default): mixed
    {
        try {
            $v = $conn->fetchOne($sql, $p);
            return ($v !== false && $v !== null) ? $v : $default;
        } catch (\Throwable) { return $default; }
    }

    // ── Role dashboards ──────────────────────────────────────────────────────

    #[IsGranted('ROLE_RH')]
    #[Route('/rh', name: 'app_rh_dashboard')]
    public function rhDashboard(): Response
    {
        return $this->render('Utilisateur/dashboard.html.twig', $this->getDashboardData());
    }

    #[IsGranted('ROLE_MANAGER')]
    #[Route('/manager', name: 'app_manager_dashboard')]
    public function managerDashboard(): Response
    {
        return $this->render('Utilisateur/dashboard.html.twig', $this->getDashboardData());
    }

    #[IsGranted('ROLE_FORMATEUR')]
    #[Route('/formateur', name: 'app_formateur_dashboard')]
    public function formateurDashboard(): Response
    {
        return $this->render('Utilisateur/dashboard.html.twig', $this->getDashboardData());
    }
}