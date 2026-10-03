<?php

namespace App\Controller\COMPETENCE;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/evaluations')]
class EvaluationsController extends AbstractController
{
    private function getCurrentUserId(): int
    {
        $user = $this->getUser();
        if (!$user || !method_exists($user, 'getId') || $user->getId() === null) {
            throw $this->createAccessDeniedException('Utilisateur non authentifie.');
        }

        return (int) $user->getId();
    }

    private function isCurrentUserAdminOrRh(): bool
    {
        return $this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_RH');
    }

    private function denyUnlessManagerRole(): void
    {
        if (!$this->isCurrentUserAdminOrRh()) {
            throw $this->createAccessDeniedException('Acces reserve aux roles RH/ADMIN.');
        }
    }

    // ══════════════════════════════════════════════════════
    //  INDEX
    // ══════════════════════════════════════════════════════
    #[Route('', name: 'evaluations_index', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $conn = $em->getConnection();

        $currentUserId = $this->getCurrentUserId();

        $search     = $request->query->get('search', '');
        $filterStat = $request->query->get('statut', '');
        $filterType = $request->query->get('type', '');

        // ── Évaluations ──────────────────────────────────────────────────
        $evals = $conn->fetchAllAssociative("
            SELECT ef.id, ef.titre, ef.type, ef.duree,
                   IFNULL(ef.score_requis, 70)   AS score_requis,
                   IFNULL(ef.niveau_succes, 1)   AS niveau_succes,
                   IFNULL(ef.niveau_echec, -1)   AS niveau_echec,
                   IFNULL(ef.competence_id, 0)   AS competence_id,
                   IFNULL(ef.niveau_requis, 0)   AS niveau_requis,
                   IFNULL(ef.difficulte, 'MOYEN') AS difficulte,
                   IFNULL(ef.statut_eval, 'ACTIVE') AS statut_eval,
                   COALESCE(ef.dateDebut, sf.dateDebut) AS eval_debut,
                   COALESCE(ef.dateFin,   sf.dateFin)   AS eval_fin,
                   f.titre AS formation_titre,
                   (SELECT COUNT(*) FROM inscriptionformation WHERE session_id = sf.id) AS nb_part,
                   (SELECT COUNT(*) FROM resultatevaluation re2 WHERE re2.evaluation_id = ef.id) AS nb_done,
                   IFNULL((SELECT re.statut    FROM resultatevaluation re WHERE re.evaluation_id = ef.id AND re.employe_id = ? LIMIT 1), 'not_taken') AS my_statut,
                   IFNULL((SELECT re.score_pct FROM resultatevaluation re WHERE re.evaluation_id = ef.id AND re.employe_id = ? LIMIT 1), 0) AS my_score
            FROM evaluationformation ef
            JOIN sessionformation sf ON ef.session_id = sf.id
            JOIN formation f         ON sf.formation_id = f.id
            ORDER BY eval_debut ASC, ef.competence_id, niveau_requis
        ", [$currentUserId, $currentUserId]);

        $today = new \DateTime();
        foreach ($evals as &$e) {
            // Date status
            $debut = $e['eval_debut'] ? new \DateTime($e['eval_debut']) : null;
            $fin   = $e['eval_fin']   ? new \DateTime($e['eval_fin'])   : null;
            if (!$debut || !$fin) {
                $e['date_status'] = 'open';
            } elseif ($today < $debut) {
                $e['date_status'] = 'not_yet';
            } elseif ($today > $fin) {
                $e['date_status'] = 'expired';
            } else {
                $e['date_status'] = 'open';
            }

            // Niveau actuel de l'employé sur cette compétence
            $compId = (int)$e['competence_id'];
            $nivActuel = 0; $nivMax = 5;
            if ($compId > 0) {
                $nivActuel = (int)($conn->fetchOne(
                    "SELECT niveauActuel FROM competenceemploye WHERE employe_id = ? AND competence_id = ?",
                    [$currentUserId, $compId]
                ) ?: 0);
                $nivMax = (int)($conn->fetchOne("SELECT niveauMax FROM competence WHERE id = ?", [$compId]) ?: 5);
            }
            $e['niveau_actuel'] = $nivActuel;
            $e['niveau_max']    = $nivMax;

            // Compétence libellé
            $e['competence_libelle'] = $compId > 0
                ? ($conn->fetchOne("SELECT libelle FROM competence WHERE id = ?", [$compId]) ?: null)
                : null;

            // Access state
            $levelOk = $compId === 0 || $nivActuel >= (int)$e['niveau_requis'];
            if (!$levelOk)                           $e['access_state'] = 'locked_level';
            elseif ($e['date_status'] === 'not_yet') $e['access_state'] = 'not_yet';
            elseif ($e['date_status'] === 'expired') $e['access_state'] = 'expired';
            else                                     $e['access_state'] = 'accessible';

            // My statut
            $e['my_statut'] = match($e['my_statut']) {
                'passed' => 'passed', 'failed' => 'failed', default => 'not_taken'
            };

            // Progression donut
            $e['pct_done'] = $e['nb_part'] > 0 ? (int)round($e['nb_done'] * 100 / $e['nb_part']) : 0;
        }
        unset($e);

        // ── Filtres ──────────────────────────────────────────────────────
        if ($search || $filterStat || $filterType) {
            $evals = array_values(array_filter($evals, function ($e) use ($search, $filterStat, $filterType) {
                if ($search && stripos($e['titre'], $search) === false && stripos($e['formation_titre'], $search) === false) return false;
                if ($filterType && strtolower($e['type']) !== strtolower($filterType)) return false;
                if ($filterStat) {
                    return match($filterStat) {
                        'accessible'   => $e['access_state'] === 'accessible',
                        'locked'       => $e['access_state'] === 'locked_level',
                        'not_yet'      => $e['access_state'] === 'not_yet',
                        'expired'      => $e['access_state'] === 'expired',
                        'passed'       => $e['my_statut']    === 'passed',
                        'failed'       => $e['my_statut']    === 'failed',
                        default        => true,
                    };
                }
                return true;
            }));
        }

        // ── Stats ────────────────────────────────────────────────────────
        $accessible  = count(array_filter($evals, fn($e) => $e['access_state'] === 'accessible' && $e['my_statut'] === 'not_taken'));
        $ceMonth     = (int)($conn->fetchOne("SELECT COUNT(*) FROM resultatevaluation WHERE MONTH(datePassage)=MONTH(CURDATE()) AND YEAR(datePassage)=YEAR(CURDATE())") ?: 0);
        $avgScore    = $conn->fetchOne("SELECT ROUND(AVG(score_pct),1) FROM resultatevaluation WHERE employe_id = ? AND score_pct > 0", [$currentUserId]);

        // ── Calendrier — évals avec dates ────────────────────────────────
        $calendarEvals = array_values(array_filter($evals, fn($e) => $e['eval_debut'] && $e['eval_fin']));

        // ── Prochaines évaluations (à venir) ─────────────────────────────
        $upcoming = array_values(array_filter($evals, fn($e) => $e['access_state'] === 'not_yet'));

        // ── Historique mes résultats ──────────────────────────────────────
        $history = $conn->fetchAllAssociative("
            SELECT re.score_pct, re.statut, re.datePassage,
                   ef.titre, ef.difficulte
            FROM resultatevaluation re
            JOIN evaluationformation ef ON re.evaluation_id = ef.id
            WHERE re.employe_id = ?
            ORDER BY re.datePassage DESC
            LIMIT 10
        ", [$currentUserId]);

        // ── Sessions pour formulaire création ────────────────────────────
        $sessions = $conn->fetchAllAssociative("
            SELECT sf.id, f.titre
            FROM sessionformation sf
            JOIN formation f ON sf.formation_id = f.id
            WHERE sf.id = (SELECT MAX(sf2.id) FROM sessionformation sf2 WHERE sf2.formation_id = f.id)
            ORDER BY f.titre ASC
        ");

        $competences = $conn->fetchAllAssociative(
            "SELECT id, libelle, niveauMax FROM competence ORDER BY libelle"
        );

        return $this->render('competence/evaluations/evaluations_index.html.twig', [
            'evals'         => $evals,
            'stats'         => [
                'accessible' => $accessible,
                'pending'    => $accessible,
                'ceMonth'    => $ceMonth,
                'avgScore'   => $avgScore ? $avgScore . '%' : '—',
            ],
            'calendarEvals' => json_encode($calendarEvals),
            'upcoming'      => $upcoming,
            'history'       => $history,
            'sessions'      => $sessions,
            'competences'   => $competences,
            'search'        => $search,
            'filterStat'    => $filterStat,
            'filterType'    => $filterType,
            'isAdmin'       => $this->isCurrentUserAdminOrRh(),
            'todayStr'      => $today->format('Y-m-d'),
        ]);
    }

    // ══════════════════════════════════════════════════════
    //  NEW EVALUATION (POST)
    // ══════════════════════════════════════════════════════
    #[Route('/new', name: 'evaluations_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyUnlessManagerRole();
        $conn = $em->getConnection();
        $r    = $request->request;

        $debut = $r->get('dateDebut') ?: null;
        $fin   = $r->get('dateFin')   ?: null;
        if ($debut && $fin && $fin < $debut) {
            $this->addFlash('error', '⚠️ La date de clôture doit être après la date d\'ouverture.');
            return $this->redirectToRoute('evaluations_index');
        }

        $conn->executeStatement("
            INSERT INTO evaluationformation
                (titre, type, duree, session_id, score_requis, niveau_succes, niveau_echec,
                 competence_id, niveau_requis, dateDebut, dateFin, difficulte)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ", [
            $r->get('titre'),
            $r->get('type', 'QCM'),
            (int) $r->get('duree', 30),
            (int) $r->get('session_id'),
            (int) $r->get('score_requis', 70),
            (int) $r->get('niveau_succes', 1),
            (int) $r->get('niveau_echec', -1),
            $r->get('competence_id') ?: null,
            (int) $r->get('niveau_requis', 0),
            $debut,
            $fin,
            $r->get('difficulte', 'MOYEN'),
        ]);
        $this->addFlash('success', '✅ Évaluation créée avec succès !');
        return $this->redirectToRoute('evaluations_index');
    }

    // ══════════════════════════════════════════════════════
    //  EDIT (POST)
    // ══════════════════════════════════════════════════════
    #[Route('/{id}/edit', name: 'evaluations_edit', methods: ['POST'])]
    public function edit(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyUnlessManagerRole();
        $conn = $em->getConnection();
        $r    = $request->request;

        $conn->executeStatement("
            UPDATE evaluationformation
            SET titre=?, type=?, duree=?, score_requis=?, niveau_succes=?, niveau_echec=?,
                competence_id=?, niveau_requis=?, dateDebut=?, dateFin=?, difficulte=?
            WHERE id=?
        ", [
            $r->get('titre'),
            $r->get('type'),
            (int)$r->get('duree'),
            (int)$r->get('score_requis', 70),
            (int)$r->get('niveau_succes', 1),
            (int)$r->get('niveau_echec', -1),
            $r->get('competence_id') ?: null,
            (int)$r->get('niveau_requis', 0),
            $r->get('dateDebut') ?: null,
            $r->get('dateFin')   ?: null,
            $r->get('difficulte', 'MOYEN'),
            $id,
        ]);
        $this->addFlash('success', '✅ Évaluation mise à jour !');
        return $this->redirectToRoute('evaluations_index');
    }

    // ══════════════════════════════════════════════════════
    //  TOGGLE STATUT (POST)
    // ══════════════════════════════════════════════════════
    #[Route('/{id}/toggle', name: 'evaluations_toggle', methods: ['POST'])]
    public function toggle(int $id, EntityManagerInterface $em): Response
    {
        $this->denyUnlessManagerRole();
        $conn    = $em->getConnection();
        $current = $conn->fetchOne("SELECT statut_eval FROM evaluationformation WHERE id = ?", [$id]);
        $new     = $current === 'INACTIVE' ? 'ACTIVE' : 'INACTIVE';
        $conn->executeStatement("UPDATE evaluationformation SET statut_eval = ? WHERE id = ?", [$new, $id]);
        $this->addFlash('success', '✅ Statut mis à jour.');
        return $this->redirectToRoute('evaluations_index');
    }

    // ══════════════════════════════════════════════════════
    //  DELETE (POST)
    // ══════════════════════════════════════════════════════
    #[Route('/{id}/delete', name: 'evaluations_delete', methods: ['POST'])]
    public function delete(int $id, EntityManagerInterface $em): Response
    {
        $this->denyUnlessManagerRole();
        $conn = $em->getConnection();
        $conn->executeStatement("DELETE FROM resultatevaluation WHERE evaluation_id = ?", [$id]);
        $conn->executeStatement("DELETE FROM evaluation_question WHERE evaluation_id = ?", [$id]);
        $conn->executeStatement("DELETE FROM evaluationformation WHERE id = ?", [$id]);
        $this->addFlash('success', '✅ Évaluation supprimée.');
        return $this->redirectToRoute('evaluations_index');
    }

    // ══════════════════════════════════════════════════════
    //  PASSER L'ÉVALUATION — questions (AJAX GET)
    // ══════════════════════════════════════════════════════
    #[Route('/{id}/questions', name: 'evaluations_questions', methods: ['GET'])]
    public function questions(int $id, EntityManagerInterface $em): JsonResponse
    {
        $conn = $em->getConnection();
        $eval = $conn->fetchAssociative(
            "SELECT id, titre, type, duree, score_requis FROM evaluationformation WHERE id = ?", [$id]
        );
        $questions = $conn->fetchAllAssociative(
            "SELECT id, question, option_a, option_b, option_c, option_d FROM evaluation_question WHERE evaluation_id = ? ORDER BY id",
            [$id]
        );
        return new JsonResponse(['eval' => $eval, 'questions' => $questions]);
    }

    // ══════════════════════════════════════════════════════
    //  SOUMETTRE RÉSULTAT (POST AJAX)
    // ══════════════════════════════════════════════════════
    #[Route('/{id}/submit', name: 'evaluations_submit', methods: ['POST'])]
    public function submit(int $id, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $conn          = $em->getConnection();
        $currentUserId = $this->getCurrentUserId();
        $answers       = $request->request->all('answers');

        $questions = $conn->fetchAllAssociative(
            "SELECT id, bonne_reponse, explication FROM evaluation_question WHERE evaluation_id = ?", [$id]
        );
        $eval = $conn->fetchAssociative(
            "SELECT score_requis, niveau_succes, niveau_echec, competence_id FROM evaluationformation WHERE id = ?", [$id]
        );

        $correct = 0;
        $results = [];
        foreach ($questions as $q) {
            $user      = strtoupper($answers[$q['id']]);
            $ok        = $user === strtoupper($q['bonne_reponse']);
            if ($ok) $correct++;
            $results[$q['id']] = ['correct' => $ok, 'bonne' => $q['bonne_reponse'], 'explication' => $q['explication']];
        }

        $total    = count($questions);
        $scorePct = $total > 0 ? (int)round($correct * 100 / $total) : 0;
        $passed   = $scorePct >= (int)$eval['score_requis'];
        $statut   = $passed ? 'passed' : 'failed';
        $delta    = $passed ? (int)$eval['niveau_succes'] : (int)$eval['niveau_echec'];

        // Enregistrer résultat
        $conn->executeStatement("
            INSERT INTO resultatevaluation (evaluation_id, employe_id, score_pct, statut, niveau_delta, datePassage)
            VALUES (?, ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE score_pct=VALUES(score_pct), statut=VALUES(statut), niveau_delta=VALUES(niveau_delta), datePassage=NOW()
        ", [$id, $currentUserId, $scorePct, $statut, $delta]);

        // Mettre à jour le niveau de compétence principale si liée
        $updatedLevels = [];
        if ($eval['competence_id'] > 0) {
            $current = (int)($conn->fetchOne(
                "SELECT niveauActuel FROM competenceemploye WHERE employe_id = ? AND competence_id = ?",
                [$currentUserId, $eval['competence_id']]
            ) ?: 0);
            $maxLevel = (int)($conn->fetchOne("SELECT niveauMax FROM competence WHERE id = ?", [$eval['competence_id']]) ?: 5);
            $newLevel = max(0, min($maxLevel, $current + $delta));
            $conn->executeStatement("
                INSERT INTO competenceemploye (employe_id, competence_id, niveauActuel, niveauValide, dateEvaluation)
                VALUES (?, ?, ?, 0, NOW())
                ON DUPLICATE KEY UPDATE niveauActuel = ?, dateEvaluation = NOW()
            ", [$currentUserId, $eval['competence_id'], $newLevel, $newLevel]);
            $libelle = $conn->fetchOne("SELECT libelle FROM competence WHERE id = ?", [$eval['competence_id']]);
            $updatedLevels[] = [
                'competence_id'  => $eval['competence_id'],
                'libelle'        => $libelle,
                'old_level'      => $current,
                'new_level'      => $newLevel,
                'delta'          => $delta,
                'max_level'      => $maxLevel,
            ];
        }

        // Mettre à jour les compétences secondaires (extra_competence_ids[])
        $extraIds = array_filter(array_map('intval', $request->request->all('extra_competence_ids')), fn($v) => $v > 0);
        foreach ($extraIds as $compId) {
            if ($compId === (int)$eval['competence_id']) continue; // already handled
            $current = (int)($conn->fetchOne(
                "SELECT niveauActuel FROM competenceemploye WHERE employe_id = ? AND competence_id = ?",
                [$currentUserId, $compId]
            ) ?: 0);
            $maxLevel = (int)($conn->fetchOne("SELECT niveauMax FROM competence WHERE id = ?", [$compId]) ?: 5);
            $newLevel = max(0, min($maxLevel, $current + $delta));
            $conn->executeStatement("
                INSERT INTO competenceemploye (employe_id, competence_id, niveauActuel, niveauValide, dateEvaluation)
                VALUES (?, ?, ?, 0, NOW())
                ON DUPLICATE KEY UPDATE niveauActuel = ?, dateEvaluation = NOW()
            ", [$currentUserId, $compId, $newLevel, $newLevel]);
            $libelle = $conn->fetchOne("SELECT libelle FROM competence WHERE id = ?", [$compId]);
            $updatedLevels[] = [
                'competence_id' => $compId,
                'libelle'       => $libelle,
                'old_level'     => $current,
                'new_level'     => $newLevel,
                'delta'         => $delta,
                'max_level'     => $maxLevel,
            ];
        }

        return new JsonResponse([
            'ok'            => true,
            'score'         => $scorePct,
            'passed'        => $passed,
            'correct'       => $correct,
            'total'         => $total,
            'delta'         => $delta,
            'results'       => $results,
            'updatedLevels' => $updatedLevels,
        ]);
    }
}