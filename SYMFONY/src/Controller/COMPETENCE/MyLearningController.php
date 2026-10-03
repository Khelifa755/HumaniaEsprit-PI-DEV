<?php

namespace App\Controller\COMPETENCE;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/mes-lecons')]
class MyLearningController extends AbstractController
{
    private function getCurrentUserId(): int
    {
        $user = $this->getUser();
        if (!$user || !method_exists($user, 'getId') || $user->getId() === null) {
            throw $this->createAccessDeniedException('Utilisateur non authentifie.');
        }

        return (int) $user->getId();
    }

    // ══════════════════════════════════════════════════════
    //  PAGE PRINCIPALE — My Learning
    // ══════════════════════════════════════════════════════
    #[Route('', name: 'mes_lecons_index', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $conn = $em->getConnection();

        $currentUserId = $this->getCurrentUserId();

        $search   = $request->query->get('search', '');
        $status   = $request->query->get('status', '');
        $category = $request->query->get('categorie', '');

        // ── Stats ────────────────────────────────────────────────────────
        $inProgress = (int)($conn->fetchOne(
            "SELECT COUNT(*) FROM inscriptionformation WHERE statut = 'In Progress' AND employe_id = ?",
            [$currentUserId]
        ) ?: 0);

        $completed = (int)($conn->fetchOne(
            "SELECT COUNT(*) FROM inscriptionformation WHERE statut = 'Completed' AND employe_id = ?",
            [$currentUserId]
        ) ?: 0);

        $totalHours = (int)($conn->fetchOne(
            "SELECT COALESCE(SUM(f.duree),0)
             FROM inscriptionformation inf
             JOIN sessionformation sf ON inf.session_id = sf.id
             JOIN formation f ON sf.formation_id = f.id
             WHERE inf.statut = 'Completed' AND inf.employe_id = ?",
            [$currentUserId]
        ) ?: 0);

        $certificates = (int)($conn->fetchOne(
            "SELECT COUNT(*) FROM inscriptionformation
             WHERE statut = 'Completed' AND noteFinale >= 70 AND employe_id = ?",
            [$currentUserId]
        ) ?: 0);

        // ── Inscriptions ─────────────────────────────────────────────────
        $sql = "SELECT inf.id, inf.statut, inf.progression, inf.noteFinale, inf.dateInscription,
                       f.id AS formation_id, f.titre, f.duree,
                       COALESCE(cf.libelle, 'Autre') AS categorie
                FROM inscriptionformation inf
                JOIN sessionformation sf  ON inf.session_id   = sf.id
                JOIN formation f          ON sf.formation_id  = f.id
                LEFT JOIN categorieformation cf ON f.categorie_id = cf.id
                WHERE inf.employe_id = ?
                ORDER BY inf.dateInscription DESC";

        try {
            $rows = $conn->fetchAllAssociative($sql, [$currentUserId]);
        } catch (\Exception $e) {
            // Fallback si session_id absent (ancienne structure)
            $rows = $conn->fetchAllAssociative(
                "SELECT inf.id, inf.statut, inf.progression, inf.noteFinale, inf.dateInscription,
                        f.id AS formation_id, f.titre, f.duree,
                        COALESCE(cf.libelle,'Autre') AS categorie
                 FROM inscriptionformation inf
                 JOIN formation f ON inf.formation_id = f.id
                 LEFT JOIN categorieformation cf ON f.categorie_id = cf.id
                 WHERE inf.employe_id = ?
                 ORDER BY inf.dateInscription DESC",
                [$currentUserId]
            );
        }

        // ── Progression dynamique ─────────────────────────────────────────
        foreach ($rows as &$row) {
            $formationId = $row['formation_id'];
            $total = (int)($conn->fetchOne(
                "SELECT COUNT(*) FROM module WHERE formation_id = ?",
                [$formationId]
            ) ?: 0);
            if ($total > 0) {
                $done = (int)($conn->fetchOne(
                    "SELECT COUNT(*) FROM module_progression mp
                     JOIN module m ON mp.module_id = m.id
                     WHERE m.formation_id = ? AND mp.employe_id = ? AND mp.statut = 'completed'",
                    [$formationId, $currentUserId]
                ) ?: 0);
                $row['progression_dynamic'] = (int) round($done * 100 / $total);
            } else {
                $row['progression_dynamic'] = (int) $row['progression'];
            }

            // Module counts
            $row['total_modules'] = $total;
            $row['done_modules']  = $total > 0 ? (int)($conn->fetchOne(
                "SELECT COUNT(*) FROM module_progression mp
                 JOIN module m ON mp.module_id = m.id
                 WHERE m.formation_id = ? AND mp.employe_id = ? AND mp.statut = 'completed'",
                [$formationId, $currentUserId]
            ) ?: 0) : 0;

            // Sync BDD si progression a changé
            if ((int)$row['progression_dynamic'] !== (int)$row['progression']) {
                $newStatut = $row['progression_dynamic'] >= 100 ? 'Completed' : 'In Progress';
                $conn->executeStatement(
                    "UPDATE inscriptionformation SET progression = ?, statut = ? WHERE id = ?",
                    [$row['progression_dynamic'], $newStatut, $row['id']]
                );
                $row['statut'] = $newStatut;
            }
        }
        unset($row);

        // ── Catégories pour filtre ────────────────────────────────────────
        $categories = array_unique(array_column($rows, 'categorie'));
        sort($categories);

        // ── Filtres ──────────────────────────────────────────────────────
        $filtered = array_filter($rows, function ($r) use ($search, $status, $category) {
            if ($search   && stripos($r['titre'], $search) === false) return false;
            if ($status   && $r['statut']    !== $status)   return false;
            if ($category && $r['categorie'] !== $category) return false;
            return true;
        });
        $filtered = array_values($filtered);

        $inProgressList = array_filter($filtered, fn($r) => $r['statut'] !== 'Completed');
        $completedList  = array_filter($filtered, fn($r) => $r['statut'] === 'Completed');

        return $this->render('competence/lecons/lecons_index.html.twig', [
            'stats'          => compact('inProgress', 'completed', 'totalHours', 'certificates'),
            'inProgressList' => array_values($inProgressList),
            'completedList'  => array_values($completedList),
            'categories'     => $categories,
            'search'         => $search,
            'filterStatus'   => $status,
            'filterCategory' => $category,
        ]);
    }

    // ══════════════════════════════════════════════════════
    //  MODULES D'UNE FORMATION → redirige vers /progress
    //  (la page avec AI + YouTube est ModuleProgressController)
    // ══════════════════════════════════════════════════════
    #[Route('/formation/{id}', name: 'mes_lecons_formation', methods: ['GET'])]
    public function formation(int $id): Response
    {
        return $this->redirectToRoute('module_progress_show', ['id' => $id]);
    }

    // ══════════════════════════════════════════════════════
    //  MARK MODULE COMPLETE (AJAX)
    // ══════════════════════════════════════════════════════
    #[Route('/module/{id}/complete', name: 'mes_lecons_module_complete', methods: ['POST'])]
    public function markComplete(int $id, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $conn          = $em->getConnection();
        $currentUserId = $this->getCurrentUserId();
        $score         = (int) $request->request->get('score_quiz', 0);

        try {
            $conn->executeStatement(
                "INSERT INTO module_progression (module_id, employe_id, statut, score_quiz, date_completion)
                 VALUES (?, ?, 'completed', ?, CURDATE())
                 ON DUPLICATE KEY UPDATE statut = 'completed', score_quiz = VALUES(score_quiz), date_completion = CURDATE()",
                [$id, $currentUserId, $score]
            );

            // Recalcul progression formation
            $formationId = $conn->fetchOne("SELECT formation_id FROM module WHERE id = ?", [$id]);
            $total       = (int)($conn->fetchOne("SELECT COUNT(*) FROM module WHERE formation_id = ?", [$formationId]) ?: 0);
            $done        = (int)($conn->fetchOne(
                "SELECT COUNT(*) FROM module_progression mp
                 JOIN module m ON mp.module_id = m.id
                 WHERE m.formation_id = ? AND mp.employe_id = ? AND mp.statut = 'completed'",
                [$formationId, $currentUserId]
            ) ?: 0);
            $pct    = $total > 0 ? (int) round($done * 100 / $total) : 0;
            $statut = $pct >= 100 ? 'Completed' : 'In Progress';

            $conn->executeStatement(
                "UPDATE inscriptionformation inf
                 JOIN sessionformation sf ON inf.session_id = sf.id
                 SET inf.progression = ?, inf.statut = ?
                 WHERE sf.formation_id = ? AND inf.employe_id = ?",
                [$pct, $statut, $formationId, $currentUserId]
            );

            return new JsonResponse(['ok' => true, 'progress' => $pct, 'statut' => $statut]);
        } catch (\Exception $e) {
            return new JsonResponse(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // ══════════════════════════════════════════════════════
    //  QUIZ SUBMIT (AJAX)
    // ══════════════════════════════════════════════════════
    #[Route('/module/{id}/quiz', name: 'mes_lecons_quiz_submit', methods: ['POST'])]
    public function quizSubmit(int $id, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $conn    = $em->getConnection();
        $answers = $request->request->all('answers'); // ['q_id' => 'A', ...]

        $questions = $conn->fetchAllAssociative(
            "SELECT id, bonne_reponse FROM quiz_question WHERE module_id = ?",
            [$id]
        );

        $correct = 0;
        $results = [];
        foreach ($questions as $q) {
            $userAnswer = $answers[$q['id']] ?? null;
            $isCorrect  = strtoupper($userAnswer ?? '') === strtoupper($q['bonne_reponse']);
            if ($isCorrect) $correct++;
            $results[$q['id']] = ['correct' => $isCorrect, 'bonne' => $q['bonne_reponse']];
        }

        $total = count($questions);
        $score = $total > 0 ? (int) round($correct * 100 / $total) : 0;

        return new JsonResponse(['ok' => true, 'score' => $score, 'correct' => $correct, 'total' => $total, 'results' => $results]);
    }

    #[Route('/module/{id}/show', name: 'mes_lecons_module_show', methods: ['GET'])]
    public function moduleShow(int $id, EntityManagerInterface $em): Response
    {
        // Redirige vers ModuleLearnerController
        return $this->redirectToRoute('module_learner_show', ['id' => $id]);
    }
}