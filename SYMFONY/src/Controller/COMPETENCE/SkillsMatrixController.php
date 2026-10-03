<?php

namespace App\Controller\COMPETENCE;

use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/competences/matrice')]
class SkillsMatrixController extends AbstractController
{
    private function getCurrentUserId(): int
    {
        $user = $this->getUser();
        if (!$user || !method_exists($user, 'getId') || $user->getId() === null) {
            throw $this->createAccessDeniedException('Utilisateur non authentifie.');
        }

        return (int) $user->getId();
    }

    private function getCurrentUserRoleName(): string
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            return 'ADMIN';
        }
        if ($this->isGranted('ROLE_RH')) {
            return 'RH';
        }
        if ($this->isGranted('ROLE_MANAGER')) {
            return 'MANAGER';
        }

        return 'EMPLOYE';
    }

    // ══════════════════════════════════════════════════════
    //  INDEX — page principale
    // ══════════════════════════════════════════════════════
    #[Route('', name: 'skills_matrix_index', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $em, PaginatorInterface $paginator): Response
    {
        $conn = $em->getConnection();

        $currentUserId   = $this->getCurrentUserId();
        $currentUserRole = $this->getCurrentUserRoleName();

        // ── Filtres ──────────────────────────────────────────────────────
        $search      = $request->query->get('search', '');
        $skillSearch = $request->query->get('skill', '');
        $filterCat   = $request->query->get('categorie', '');
        $gapsOnly    = (bool) $request->query->get('gaps_only', false);
        $empPerPage  = max(1, min(50, (int) $request->query->get('emp_per_page', 5)));
        $skillPerPage = max(1, min(20, (int) $request->query->get('skill_per_page', 5)));

        // ── Stats ────────────────────────────────────────────────────────
        $stats = $this->loadStats($conn, $currentUserId, $currentUserRole);

        // ── Catégories ───────────────────────────────────────────────────
        $categories = $conn->fetchAllAssociative(
            "SELECT DISTINCT libelle FROM categorieCompetence ORDER BY libelle"
        );

        // ── Compétences (colonnes) ───────────────────────────────────────
        $compSql    = "SELECT DISTINCT c.id, c.libelle, c.niveauMax,
                              COALESCE(cc.libelle,'Général') AS categorie
                       FROM competence c
                       LEFT JOIN categorieCompetence cc ON c.categorie_id = cc.id
                       WHERE c.statutCompetence = 'ACTIF'";
        $compParams = [];
        if ($filterCat) {
            $compSql   .= " AND cc.libelle = ?";
            $compParams[] = $filterCat;
        }
        if ($skillSearch) {
            $compSql   .= " AND c.libelle LIKE ?";
            $compParams[] = "%$skillSearch%";
        }
        $compSql .= " ORDER BY cc.libelle, c.libelle";

        $allCompetences = $conn->fetchAllAssociative($compSql, $compParams);

        // ── Employés (lignes) ────────────────────────────────────────────
        $empWhere  = [];
        $empParams = [];

        if ($currentUserRole === 'MANAGER') {
            $empWhere[]   = "u.manager_id = ?";
            $empParams[]  = $currentUserId;
            $empWhere[]   = "u.statut = 'Actif'";
        } elseif ($currentUserRole === 'EMPLOYE') {
            $empWhere[]  = "u.id = ?";
            $empParams[] = $currentUserId;
        } else {
            $empWhere[] = "u.role = 'EMPLOYE'";
            $empWhere[] = "u.statut = 'Actif'";
        }

        if ($search) {
            $empWhere[]  = "(CONCAT(u.prenom,' ',u.nom) LIKE ? OR COALESCE(u.posteActuel,u.role) LIKE ?)";
            $empParams[] = "%$search%";
            $empParams[] = "%$search%";
        }

        $empWhereStr = count($empWhere) > 0 ? "WHERE " . implode(" AND ", $empWhere) : "";

        $allEmployees = $conn->fetchAllAssociative("
            SELECT u.id,
                   CONCAT(u.prenom,' ',u.nom)       AS fullName,
                   COALESCE(u.posteActuel, u.role)   AS poste
            FROM utilisateur u
            $empWhereStr
            ORDER BY u.nom, u.prenom
        ", $empParams);

        // Charger les compétences de chaque employé
        foreach ($allEmployees as &$emp) {
            $skills = $conn->fetchAllAssociative("
                SELECT c.libelle, ce.niveauActuel, c.niveauMax, ce.niveauValide
                FROM competenceemploye ce
                JOIN competence c ON ce.competence_id = c.id
                WHERE ce.employe_id = ? AND c.statutCompetence = 'ACTIF'
            ", [$emp['id']]);
            $emp['skills'] = [];
            foreach ($skills as $s) {
                $emp['skills'][$s['libelle']] = [
                    'niveau'    => (int)  $s['niveauActuel'],
                    'niveauMax' => (int) ($s['niveauMax'] ?: 5),
                    'valide'    => (bool) $s['niveauValide'],
                ];
            }
        }
        unset($emp);

        // Filtre gaps seulement
        if ($gapsOnly) {
            $allEmployees = array_filter($allEmployees, function ($emp) {
                foreach ($emp['skills'] as $s) {
                    if ($s['niveau'] < 3) return true;
                }
                return false;
            });
            $allEmployees = array_values($allEmployees);
        }

        // ── Pagination KnpPaginator ───────────────────────────────────────
        // Employés : paginator standard sur le tableau
        $empPagination = $paginator->paginate(
            $allEmployees,
            $request->query->getInt('emp_page', 1),
            $empPerPage
        );

        // Compétences : paginator avec paramètre dédié
        $skillPagination = $paginator->paginate(
            $allCompetences,
            $request->query->getInt('skill_page', 1),
            $skillPerPage,
            ['pageParameterName' => 'skill_page']
        );

        // Pour le tableau on a besoin des tableaux paginés
        $pageEmployees   = iterator_to_array($empPagination->getItems());
        $pageCompetences = iterator_to_array($skillPagination->getItems());

        return $this->render('competence/matrice/matrice_index.html.twig', [
            'stats'           => $stats,
            'categories'      => $categories,
            'pageCompetences' => $pageCompetences,
            'allCompetences'  => $allCompetences,
            'pageEmployees'   => $pageEmployees,
            'empPagination'   => $empPagination,
            'skillPagination' => $skillPagination,
            'search'          => $search,
            'skillSearch'     => $skillSearch,
            'filterCat'       => $filterCat,
            'gapsOnly'        => $gapsOnly,
            'empPerPage'      => $empPerPage,
            'skillPerPage'    => $skillPerPage,
            'skillOffset'     => ($skillPagination->getCurrentPageNumber() - 1) * $skillPerPage,
            'role'            => $currentUserRole,
        ]);
    }

    // ══════════════════════════════════════════════════════
    //  ADD / UPDATE ASSESSMENT (AJAX)
    // ══════════════════════════════════════════════════════
    #[Route('/assessment/save', name: 'skills_matrix_save', methods: ['POST'])]
    public function saveAssessment(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $conn        = $em->getConnection();
        $employeId   = (int) $request->request->get('employe_id');
        $competenceId= (int) $request->request->get('competence_id');
        $niveau      = max(1, min(5, (int) $request->request->get('niveau', 1)));
        $valide      = (bool) $request->request->get('valide', false);
        $date        = $request->request->get('date', date('Y-m-d'));

        if (!$employeId || !$competenceId) {
            return new JsonResponse(['ok' => false, 'error' => 'Données manquantes'], 400);
        }

        try {
            $conn->executeStatement("
                INSERT INTO competenceemploye
                    (niveauActuel, niveauValide, dateEvaluation, employe_id, competence_id)
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    niveauActuel   = VALUES(niveauActuel),
                    niveauValide   = VALUES(niveauValide),
                    dateEvaluation = VALUES(dateEvaluation)
            ", [$niveau, $valide ? 1 : 0, $date, $employeId, $competenceId]);

            return new JsonResponse(['ok' => true]);
        } catch (\Exception $e) {
            return new JsonResponse(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // ══════════════════════════════════════════════════════
    //  DELETE ASSESSMENT (AJAX)
    // ══════════════════════════════════════════════════════
    #[Route('/assessment/delete', name: 'skills_matrix_delete', methods: ['POST'])]
    public function deleteAssessment(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $conn         = $em->getConnection();
        $employeId    = (int) $request->request->get('employe_id');
        $competenceId = (int) $request->request->get('competence_id');

        try {
            $conn->executeStatement(
                "DELETE FROM competenceemploye WHERE employe_id = ? AND competence_id = ?",
                [$employeId, $competenceId]
            );
            return new JsonResponse(['ok' => true]);
        } catch (\Exception $e) {
            return new JsonResponse(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // ══════════════════════════════════════════════════════
    //  EXPORT CSV
    // ══════════════════════════════════════════════════════
    #[Route('/export/csv', name: 'skills_matrix_export_csv', methods: ['GET'])]
    public function exportCsv(EntityManagerInterface $em): Response
    {
        $conn = $em->getConnection();

        $competences = $conn->fetchAllAssociative(
            "SELECT libelle FROM competence WHERE statutCompetence = 'ACTIF' ORDER BY libelle"
        );
        $employees = $conn->fetchAllAssociative("
            SELECT u.id, CONCAT(u.prenom,' ',u.nom) AS fullName,
                   COALESCE(u.posteActuel, u.role) AS poste
            FROM utilisateur u
            WHERE u.role = 'EMPLOYE' AND u.statut = 'Actif'
            ORDER BY u.nom
        ");

        foreach ($employees as &$emp) {
            $skills = $conn->fetchAllAssociative(
                "SELECT c.libelle, ce.niveauActuel
                 FROM competenceemploye ce
                 JOIN competence c ON ce.competence_id = c.id
                 WHERE ce.employe_id = ?",
                [$emp['id']]
            );
            $emp['skills'] = [];
            foreach ($skills as $s) {
                $emp['skills'][$s['libelle']] = $s['niveauActuel'];
            }
        }
        unset($emp);

        $csv  = "\xEF\xBB\xBF"; // BOM UTF-8
        $csv .= $this->csvCell('Employé') . ',' . $this->csvCell('Poste');
        foreach ($competences as $c) {
            $csv .= ',' . $this->csvCell($c['libelle']);
        }
        $csv .= "\n";

        foreach ($employees as $emp) {
            $csv .= $this->csvCell($emp['fullName']) . ',' . $this->csvCell($emp['poste']);
            foreach ($competences as $c) {
                $val = $emp['skills'][$c['libelle']] ?? '-';
                $csv .= ',' . $this->csvCell((string) $val);
            }
            $csv .= "\n";
        }

        $filename = 'skills_matrix_' . date('Y-m-d') . '.csv';
        return new Response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    // ══════════════════════════════════════════════════════
    //  RADAR DATA (AJAX — pour le graphe Chart.js)
    // ══════════════════════════════════════════════════════
    #[Route('/radar/{employeId}', name: 'skills_matrix_radar', methods: ['GET'])]
    public function radarData(int $employeId, EntityManagerInterface $em): JsonResponse
    {
        $conn = $em->getConnection();

        $skills = $conn->fetchAllAssociative("
            SELECT c.libelle, COALESCE(cc.libelle,'Général') AS categorie,
                   ce.niveauActuel, c.niveauMax, ce.niveauValide
            FROM competenceemploye ce
            JOIN competence c ON ce.competence_id = c.id
            LEFT JOIN categorieCompetence cc ON c.categorie_id = cc.id
            WHERE ce.employe_id = ?
            ORDER BY cc.libelle, c.libelle
            LIMIT 12
        ", [$employeId]);

        $avg = $conn->fetchAllAssociative("
            SELECT c.libelle, AVG(ce.niveauActuel) AS avgNiveau, c.niveauMax
            FROM competenceemploye ce
            JOIN competence c ON ce.competence_id = c.id
            GROUP BY c.id, c.libelle, c.niveauMax
            ORDER BY c.libelle
        ");
        $avgMap = [];
        foreach ($avg as $a) {
            $avgMap[$a['libelle']] = round((float)$a['avgNiveau'], 2);
        }

        $employeInfo = $conn->fetchAssociative(
            "SELECT CONCAT(prenom,' ',nom) AS fullName FROM utilisateur WHERE id = ?",
            [$employeId]
        );

        return new JsonResponse([
            'employe' => $employeInfo['fullName'] ?? 'Employé',
            'skills'  => $skills,
            'avgMap'  => $avgMap,
        ]);
    }

    // ══════════════════════════════════════════════════════
    //  HELPERS PRIVÉS
    // ══════════════════════════════════════════════════════
    private function loadStats(\Doctrine\DBAL\Connection $conn, int $userId, string $role): array
    {
        if ($role === 'EMPLOYE') {
            $teamMembers = 1;
            $gaps        = (int)($conn->fetchOne(
                "SELECT COUNT(*) FROM competenceemploye ce
                 JOIN competence c ON ce.competence_id = c.id
                 WHERE ce.employe_id = ? AND ce.niveauActuel < 3 AND c.statutCompetence = 'ACTIF'",
                [$userId]
            ) ?: 0);
            $avgLevel    = (float)($conn->fetchOne(
                "SELECT AVG(niveauActuel) FROM competenceemploye WHERE employe_id = ?",
                [$userId]
            ) ?: 0);
            $totalSkills = (int)($conn->fetchOne(
                "SELECT COUNT(*) FROM competenceemploye WHERE employe_id = ?",
                [$userId]
            ) ?: 0);
        } elseif ($role === 'MANAGER') {
            $teamMembers = (int)($conn->fetchOne(
                "SELECT COUNT(*) FROM utilisateur WHERE manager_id = ? AND statut = 'Actif'",
                [$userId]
            ) ?: 0);
            $gaps = (int)($conn->fetchOne(
                "SELECT COUNT(DISTINCT ce.competence_id) FROM competenceemploye ce
                 JOIN utilisateur u ON ce.employe_id = u.id
                 WHERE u.manager_id = ? AND ce.niveauActuel < 3",
                [$userId]
            ) ?: 0);
            $avgLevel = (float)($conn->fetchOne(
                "SELECT AVG(ce.niveauActuel) FROM competenceemploye ce
                 JOIN utilisateur u ON ce.employe_id = u.id WHERE u.manager_id = ?",
                [$userId]
            ) ?: 0);
            $totalSkills = (int)($conn->fetchOne(
                "SELECT COUNT(*) FROM competence WHERE statutCompetence = 'ACTIF'"
            ) ?: 0);
        } else {
            $teamMembers = (int)($conn->fetchOne(
                "SELECT COUNT(*) FROM utilisateur WHERE role = 'EMPLOYE' AND statut = 'Actif'"
            ) ?: 0);
            $gaps = (int)($conn->fetchOne(
                "SELECT COUNT(DISTINCT competence_id) FROM competenceemploye WHERE niveauActuel < 3"
            ) ?: 0);
            $avgLevel = (float)($conn->fetchOne(
                "SELECT AVG(niveauActuel) FROM competenceemploye"
            ) ?: 0);
            $totalSkills = (int)($conn->fetchOne(
                "SELECT COUNT(*) FROM competence WHERE statutCompetence = 'ACTIF'"
            ) ?: 0);
        }

        return [
            'teamMembers' => $teamMembers,
            'gaps'        => $gaps,
            'avgLevel'    => number_format($avgLevel, 1),
            'totalSkills' => $totalSkills,
        ];
    }

    private function csvCell(?string $value): string
    {
        if ($value === null) return '""';
        return '"' . str_replace('"', '""', $value) . '"';
    }
}