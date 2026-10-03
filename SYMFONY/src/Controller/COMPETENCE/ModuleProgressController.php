<?php

namespace App\Controller\COMPETENCE;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Vue détaillée d'une formation avec progression, IA, YouTube, PDF,
 * et désormais l'Examen Final (50 QCM) quand tous les modules sont terminés.
 *
 * URL de base : /mes-lecons/formation/{id}/progress
 */
#[Route('/mes-lecons/formation/{id}/progress', name: 'module_progress_')]
class ModuleProgressController extends AbstractController
{
    private static string $GROQ_URL = 'https://api.groq.com/openai/v1/chat/completions';
    private const PASS_SCORE        = 70;   // % requis pour réussir
    private const EXAM_SIZE         = 50;   // nombre de questions

    private function getCurrentUserId(): int
    {
        $user = $this->getUser();
        if (!$user || !method_exists($user, 'getId') || $user->getId() === null) {
            throw $this->createAccessDeniedException('Utilisateur non authentifie.');
        }

        return (int) $user->getId();
    }

    // ══════════════════════════════════════════════════════
    //  PAGE PRINCIPALE — MODULE PROGRESS
    // ══════════════════════════════════════════════════════
    #[Route('', name: 'show', methods: ['GET'])]
    public function show(int $id, EntityManagerInterface $em): Response
    {
        $conn          = $em->getConnection();
        $currentUserId = $this->getCurrentUserId();

        // ── Formation ──────────────────────────────────────────────────
        $formation = $conn->fetchAssociative(
            "SELECT f.id, f.titre, f.duree, f.typeFormation,
                    COALESCE(cf.libelle,'Autre') AS categorie,
                    COALESCE(CONCAT(u.prenom,' ',u.nom), 'N/A') AS formateur_nom
             FROM formation f
             LEFT JOIN categorieformation cf ON f.categorie_id = cf.id
             LEFT JOIN utilisateur u ON f.formateur_id = u.id
             WHERE f.id = ?",
            [$id]
        );
        if (!$formation) throw $this->createNotFoundException("Formation #$id introuvable.");

        // ── Inscription ────────────────────────────────────────────────
        $inscription = $conn->fetchAssociative(
            "SELECT inf.id, inf.progression, inf.statut, inf.noteFinale
             FROM inscriptionformation inf
             JOIN sessionformation sf ON inf.session_id = sf.id
             WHERE sf.formation_id = ? AND inf.employe_id = ? LIMIT 1",
            [$id, $currentUserId]
        );

        // ── Modules avec progression ───────────────────────────────────
        $modules = $conn->fetchAllAssociative(
            "SELECT m.id, m.titre, m.description, m.type_contenu,
                    m.duree_minutes, m.ordre,
                    COALESCE(m.contenu_texte, '') AS contenu_texte,
                    COALESCE(m.url_ressource, '') AS url_ressource,
                    COALESCE(mp.statut, 'not_started') AS statut,
                    COALESCE(mp.score_quiz, 0) AS score
             FROM module m
             LEFT JOIN module_progression mp
                ON mp.module_id = m.id AND mp.employe_id = ?
             WHERE m.formation_id = ?
             ORDER BY m.ordre ASC",
            [$currentUserId, $id]
        );

        // ── Calcul progression ─────────────────────────────────────────
        $total    = count($modules);
        $done     = count(array_filter($modules, fn($m) => $m['statut'] === 'completed'));
        $progress = $total > 0 ? (int) round($done * 100 / $total) : 0;

        // Sync inscription
        if ($inscription) {
            $newStatut = $progress >= 100 ? 'Completed' : 'In Progress';
            $conn->executeStatement(
                "UPDATE inscriptionformation SET progression = ?, statut = ? WHERE id = ?",
                [$progress, $newStatut, $inscription['id']]
            );
        }

        // ── Règle de déverrouillage séquentielle ──────────────────────
        foreach ($modules as $i => &$mod) {
            $mod['unlocked'] = ($i === 0) || ($modules[$i - 1]['statut'] === 'completed');
        }
        unset($mod);

        // ── Examen final ───────────────────────────────────────────────
        $allComplete = ($progress >= 100);

        // Dernier résultat d'examen final de cet utilisateur pour cette formation
        $lastExam = null;
        try {
            $lastExam = $conn->fetchAssociative(
                "SELECT score_pct, passed, nb_correct, nb_total, attempt_number, date_passage
                 FROM formation_exam_result
                 WHERE formation_id = ? AND employe_id = ?
                 ORDER BY date_passage DESC LIMIT 1",
                [$id, $currentUserId]
            );
        } catch (\Exception $e) {
            // table pas encore créée – on ignore
        }

        // Nombre de questions disponibles pour cet examen
        $examQCount = 0;
        try {
            $examQCount = (int)($conn->fetchOne(
                "SELECT COUNT(*) FROM formation_final_exam_question WHERE formation_id = ?",
                [$id]
            ) ?: 0);
        } catch (\Exception $e) {
        }

        return $this->render('competence/lecons/module_progress.html.twig', [
            'formation'    => $formation,
            'modules'      => $modules,
            'inscription'  => $inscription,
            'progress'     => $progress,
            'doneMods'     => $done,
            'totalMods'    => $total,
            'userId'       => $currentUserId,
            // Examen final
            'allComplete'  => $allComplete,
            'lastExam'     => $lastExam,
            'examQCount'   => $examQCount,
            'passScore'    => self::PASS_SCORE,
        ]);
    }

    // ══════════════════════════════════════════════════════
    //  FINAL EXAM — charger les questions (AJAX GET)
    // ══════════════════════════════════════════════════════
    #[Route('/final-exam/questions', name: 'final_exam_questions', methods: ['GET'])]
    public function finalExamQuestions(int $id, EntityManagerInterface $em): JsonResponse
    {
        $conn = $em->getConnection();

        // Priorité 1 : questions dédiées à l'examen final (table formation_final_exam_question)
        try {
            $questions = $conn->fetchAllAssociative(
                "SELECT id, question, option_a, option_b, option_c, option_d, module_ref
                 FROM formation_final_exam_question
                 WHERE formation_id = ?
                 ORDER BY RAND()
                 LIMIT " . self::EXAM_SIZE,
                [$id]
            );
        } catch (\Exception $e) {
            $questions = [];
        }

        // Priorité 2 : si pas assez de questions dédiées, compléter avec quiz_question des modules
        if (count($questions) < self::EXAM_SIZE) {
            $needed = self::EXAM_SIZE - count($questions);
            $existingIds = array_column($questions, 'id');

            $excludeClause = '';
            $params = [$id];
            if (!empty($existingIds)) {
                $placeholders = implode(',', array_fill(0, count($existingIds), '?'));
                $excludeClause = "AND qq.id NOT IN ($placeholders)";
                $params = array_merge($params, $existingIds);
            }

            $extra = $conn->fetchAllAssociative(
                "SELECT qq.id, qq.question, qq.option_a, qq.option_b, qq.option_c, qq.option_d,
                        m.titre AS module_ref
                 FROM quiz_question qq
                 JOIN module m ON qq.module_id = m.id
                 WHERE m.formation_id = ? $excludeClause
                 ORDER BY RAND()
                 LIMIT $needed",
                $params
            );
            $questions = array_merge($questions, $extra);
        }

        // Priorité 3 : si toujours pas assez, générer des questions IA
        if (count($questions) < 5) {
            return new JsonResponse([
                'ok'        => false,
                'error'     => 'Pas assez de questions disponibles pour cet examen. Ajoutez des questions quiz aux modules.',
                'count'     => count($questions),
            ], 422);
        }

        // On mélange et on limite à EXAM_SIZE
        shuffle($questions);
        $questions = array_slice($questions, 0, self::EXAM_SIZE);

        return new JsonResponse([
            'ok'         => true,
            'questions'  => $questions,
            'total'      => count($questions),
            'pass_score' => self::PASS_SCORE,
        ]);
    }

    // ══════════════════════════════════════════════════════
    //  FINAL EXAM — soumettre les réponses (AJAX POST)
    // ══════════════════════════════════════════════════════
    #[Route('/final-exam/submit', name: 'final_exam_submit', methods: ['POST'])]
    public function finalExamSubmit(int $id, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $conn          = $em->getConnection();
        $currentUserId = $this->getCurrentUserId();

        $answers = $request->request->all('answers'); // ['q_id' => 'A', ...]

        // ── Récupérer les bonnes réponses depuis les 2 tables ─────────
        // On cherche d'abord dans formation_final_exam_question, puis quiz_question
        $questionIds = array_map('intval', array_keys($answers));
        if (empty($questionIds)) {
            return new JsonResponse(['ok' => false, 'error' => 'Aucune réponse soumise'], 400);
        }

        $placeholders = implode(',', array_fill(0, count($questionIds), '?'));

        $correctMap = [];

        // Table dédiée exam
        try {
            $rows = $conn->fetchAllAssociative(
                "SELECT id, bonne_reponse, explication, module_ref
                 FROM formation_final_exam_question
                 WHERE formation_id = ? AND id IN ($placeholders)",
                array_merge([$id], $questionIds)
            );
            foreach ($rows as $r) {
                $correctMap[$r['id']] = [
                    'bonne'       => strtoupper($r['bonne_reponse']),
                    'explication' => $r['explication'],
                    'module'      => $r['module_ref'] ?? '',
                    'source'      => 'exam',
                ];
            }
        } catch (\Exception $e) {
        }

        // Compléter avec quiz_question pour les ids manquants
        $missing = array_diff($questionIds, array_keys($correctMap));
        if (!empty($missing)) {
            $mp = implode(',', array_fill(0, count($missing), '?'));
            $rows2 = $conn->fetchAllAssociative(
                "SELECT qq.id, qq.bonne_reponse, qq.explication, m.titre AS module_ref
                 FROM quiz_question qq
                 JOIN module m ON qq.module_id = m.id
                 WHERE m.formation_id = ? AND qq.id IN ($mp)",
                array_merge([$id], array_values($missing))
            );
            foreach ($rows2 as $r) {
                $correctMap[$r['id']] = [
                    'bonne'       => strtoupper($r['bonne_reponse']),
                    'explication' => $r['explication'],
                    'module'      => $r['module_ref'] ?? '',
                    'source'      => 'quiz',
                ];
            }
        }

        // ── Calcul score ───────────────────────────────────────────────
        $correct = 0;
        $results = [];
        foreach ($answers as $qId => $userAnswer) {
            $qId = (int) $qId;
            $bonneReponse = $correctMap[$qId]['bonne'] ?? null;
            $isOk = ($bonneReponse !== null) && (strtoupper($userAnswer) === $bonneReponse);
            if ($isOk) $correct++;
            $results[$qId] = [
                'correct'     => $isOk,
                'user'        => strtoupper($userAnswer),
                'bonne'       => $bonneReponse ?? '?',
                'explication' => $correctMap[$qId]['explication'] ?? '',
                'module'      => $correctMap[$qId]['module'] ?? '',
            ];
        }

        $total    = count($answers);
        $scorePct = $total > 0 ? (int) round($correct * 100 / $total) : 0;
        $passed   = $scorePct >= self::PASS_SCORE;

        // ── Numéro de tentative ────────────────────────────────────────
        $attemptNumber = 1;
        try {
            $lastAttempt = $conn->fetchOne(
                "SELECT MAX(attempt_number) FROM formation_exam_result
                 WHERE formation_id = ? AND employe_id = ?",
                [$id, $currentUserId]
            );
            if ($lastAttempt) $attemptNumber = (int)$lastAttempt + 1;
        } catch (\Exception $e) {
        }

        // ── Enregistrer le résultat ────────────────────────────────────
        $competenceUpdated = false;
        try {
            $conn->executeStatement(
                "INSERT INTO formation_exam_result
                    (formation_id, employe_id, score_pct, nb_correct, nb_total,
                     passed, attempt_number, date_passage, competence_updated)
                 VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), 0)",
                [$id, $currentUserId, $scorePct, $correct, $total, $passed ? 1 : 0, $attemptNumber]
            );
            $examResultId = (int)$conn->lastInsertId();
        } catch (\Exception $e) {
            // table pas créée – on continue sans persistance
            $examResultId = 0;
        }

        // ── Mise à jour du niveau de compétence si réussite ───────────
        $updatedLevels = [];
        if ($passed) {
            // Trouver les compétences liées aux évaluations de cette formation
            $linkedComps = $conn->fetchAllAssociative(
                "SELECT DISTINCT ef.competence_id, c.libelle, c.niveauMax
                 FROM evaluationformation ef
                 JOIN sessionformation sf ON ef.session_id = sf.id
                 JOIN competence c ON ef.competence_id = c.id
                 WHERE sf.formation_id = ? AND ef.competence_id IS NOT NULL",
                [$id]
            );

            foreach ($linkedComps as $comp) {
                $compId = (int) $comp['competence_id'];
                $niveauMax = (int) $comp['niveauMax'];

                $current = (int)($conn->fetchOne(
                    "SELECT niveauActuel FROM competenceemploye
                     WHERE employe_id = ? AND competence_id = ?",
                    [$currentUserId, $compId]
                ) ?: 0);

                // +1 niveau si réussite (max = niveauMax)
                $newLevel = min($niveauMax, $current + 1);

                $conn->executeStatement(
                    "INSERT INTO competenceemploye
                        (employe_id, competence_id, niveauActuel, niveauValide, dateEvaluation)
                     VALUES (?, ?, ?, 1, CURDATE())
                     ON DUPLICATE KEY UPDATE
                        niveauActuel   = GREATEST(niveauActuel, ?),
                        niveauValide   = 1,
                        dateEvaluation = CURDATE()",
                    [$currentUserId, $compId, $newLevel, $newLevel]
                );

                $updatedLevels[] = [
                    'competence_id' => $compId,
                    'libelle'       => $comp['libelle'],
                    'old_level'     => $current,
                    'new_level'     => $newLevel,
                    'max_level'     => $niveauMax,
                ];
                $competenceUpdated = true;
            }

            // Marquer la colonne competence_updated
            if ($examResultId && $competenceUpdated) {
                try {
                    $conn->executeStatement(
                        "UPDATE formation_exam_result SET competence_updated = 1 WHERE id = ?",
                        [$examResultId]
                    );
                } catch (\Exception $e) {
                }
            }

            // Mettre à jour noteFinale de l'inscription
            try {
                $conn->executeStatement(
                    "UPDATE inscriptionformation inf
                     JOIN sessionformation sf ON inf.session_id = sf.id
                     SET inf.noteFinale = ?, inf.statut = 'Completed'
                     WHERE sf.formation_id = ? AND inf.employe_id = ?",
                    [$scorePct, $id, $currentUserId]
                );
            } catch (\Exception $e) {
            }
        }

        return new JsonResponse([
            'ok'             => true,
            'score'          => $scorePct,
            'correct'        => $correct,
            'total'          => $total,
            'passed'         => $passed,
            'pass_score'     => self::PASS_SCORE,
            'attempt'        => $attemptNumber,
            'results'        => $results,
            'updatedLevels'  => $updatedLevels,
        ]);
    }

    // ══════════════════════════════════════════════════════
    //  SKILLS GAP ANALYSIS (AJAX)
    // ══════════════════════════════════════════════════════
    #[Route('/skills-gap', name: 'skills_gap', methods: ['POST'])]
    public function skillsGap(int $id, EntityManagerInterface $em): JsonResponse
    {
        $conn          = $em->getConnection();
        $currentUserId = $this->getCurrentUserId();

        $formation = $conn->fetchAssociative(
            "SELECT titre, typeFormation FROM formation WHERE id = ?",
            [$id]
        );

        $modules = $conn->fetchAllAssociative(
            "SELECT m.titre, m.type_contenu, COALESCE(mp.statut,'not_started') AS statut
             FROM module m
             LEFT JOIN module_progression mp ON mp.module_id = m.id AND mp.employe_id = ?
             WHERE m.formation_id = ?
             ORDER BY m.ordre ASC",
            [$currentUserId, $id]
        );

        $userSkills = $conn->fetchAllAssociative(
            "SELECT c.libelle AS nom FROM competence c
            JOIN competenceemploye ec ON ec.competence_id = c.id
            WHERE ec.employe_id = ?",
            [$currentUserId]
        );

        $skillsList  = implode(', ', array_column($userSkills, 'nom')) ?: 'Aucune compétence listée';
        $modulesList = implode(', ', array_column($modules, 'titre'));
        $completedMods = count(array_filter($modules, fn($m) => $m['statut'] === 'completed'));
        $totalMods     = count($modules);

        $prompt = "Analyse les écarts de compétences pour cet employé en français.
            Formation : {$formation['titre']} ({$formation['typeFormation']})
            Modules : $modulesList
            Progression : $completedMods/$totalMods modules terminés
            Compétences actuelles : $skillsList

            Fournis :
            1. POINTS FORTS : compétences déjà maîtrisées
            2. GAPS IDENTIFIÉS : ce qui manque selon la formation
            3. PRIORITÉS : les 3 modules les plus importants à terminer
            4. RECOMMANDATIONS : conseils pratiques

            Sois concis et structuré.";

        try {
            $apiKey = $this->getParameter('groq_api_key');
            $resolvedKey = (isset($_ENV['GROQ_API_KEY']) && $_ENV['GROQ_API_KEY'] !== '')
                ? $_ENV['GROQ_API_KEY']
                : (getenv('GROQ_API_KEY') ?: $apiKey);
            $result = $this->callGroq($resolvedKey, $prompt);
            return new JsonResponse(['ok' => true, 'result' => $result]);
        } catch (\Exception $e) {
            return new JsonResponse(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // ══════════════════════════════════════════════════════
    //  GENERATE PDI (AJAX)
    // ══════════════════════════════════════════════════════
    #[Route('/pdi', name: 'pdi', methods: ['POST'])]
    public function generatePdi(int $id, EntityManagerInterface $em): JsonResponse
    {
        $conn          = $em->getConnection();
        $currentUserId = $this->getCurrentUserId();

        $formation = $conn->fetchAssociative(
            "SELECT titre FROM formation WHERE id = ?",
            [$id]
        );

        $modules = $conn->fetchAllAssociative(
            "SELECT m.titre, m.type_contenu,
                    COALESCE(mp.statut,'not_started') AS statut,
                    COALESCE(mp.score_quiz,0) AS score
             FROM module m
             LEFT JOIN module_progression mp ON mp.module_id = m.id AND mp.employe_id = ?
             WHERE m.formation_id = ?
             ORDER BY m.ordre ASC",
            [$currentUserId, $id]
        );

        $moduleLines = array_map(
            fn($m) =>
            "- {$m['titre']} ({$m['type_contenu']}) : {$m['statut']}" .
                ($m['score'] > 0 ? " — score quiz {$m['score']}%" : ""),
            $modules
        );

        $prompt = "Génère un Plan de Développement Individuel (PDI) structuré en français pour :
            Formation : {$formation['titre']}
            Progression détaillée :
            " . implode("\n", $moduleLines) . "

            Structure du PDI :
        1. OBJECTIFS (court/moyen/long terme)
        2. ACTIONS PRIORITAIRES (les 3 prochaines étapes concrètes)
        3. RESSOURCES SUGGÉRÉES (types de ressources, pratiques)
        4. INDICATEURS DE SUCCÈS (métriques mesurables)
        5. CALENDRIER SUGGÉRÉ (estimation réaliste)

        Sois précis et actionnable.";

        try {
            $apiKey = (isset($_ENV['GROQ_API_KEY']) && $_ENV['GROQ_API_KEY'] !== '')
                ? $_ENV['GROQ_API_KEY']
                : (getenv('GROQ_API_KEY') ?: '');
            $result = $this->callGroq($apiKey, $prompt);
            return new JsonResponse(['ok' => true, 'result' => $result]);
        } catch (\Exception $e) {
            return new JsonResponse(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // ══════════════════════════════════════════════════════
    //  YOUTUBE SUGGESTIONS (AJAX)
    // ══════════════════════════════════════════════════════
    #[Route('/youtube', name: 'youtube', methods: ['GET'])]
    public function youtubeSuggestions(int $id, EntityManagerInterface $em): JsonResponse
    {
        $conn      = $em->getConnection();
        $formation = $conn->fetchAssociative("SELECT titre FROM formation WHERE id = ?", [$id]);
        if (!$formation) return new JsonResponse(['ok' => false, 'error' => 'Formation introuvable'], 404);

        $youtubeKey = $this->getParameter('youtube_api_key');
        $query      = urlencode($formation['titre'] . ' formation cours');
        $url        = "https://www.googleapis.com/youtube/v3/search?part=snippet&q={$query}&type=video&maxResults=6&relevanceLanguage=fr&key={$youtubeKey}";

        try {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 10,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 0,
            ]);
            $body = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($code !== 200) throw new \RuntimeException("YouTube API error $code : $body");

            $data   = json_decode($body, true);
            $videos = [];
            foreach ($data['items'] ?? [] as $item) {
                $videos[] = [
                    'videoId'     => $item['id']['videoId'],
                    'title'       => $item['snippet']['title'],
                    'thumbnail'   => $item['snippet']['thumbnails']['medium']['url'] ?? '',
                    'channelName' => $item['snippet']['channelTitle'],
                    'embedUrl'    => 'https://www.youtube.com/embed/' . $item['id']['videoId'],
                    'url'         => 'https://www.youtube.com/watch?v=' . $item['id']['videoId'],
                ];
            }

            return new JsonResponse(['ok' => true, 'videos' => $videos]);
        } catch (\Exception $e) {
            return new JsonResponse(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // ══════════════════════════════════════════════════════
    //  EXPORT PDF
    // ══════════════════════════════════════════════════════
    #[Route('/export-pdf', name: 'export_pdf', methods: ['GET'])]
    public function exportPdf(int $id, EntityManagerInterface $em): Response
    {
        $conn          = $em->getConnection();
        $currentUserId = $this->getCurrentUserId();

        $formation = $conn->fetchAssociative(
            "SELECT f.titre, f.duree, COALESCE(cf.libelle,'Autre') AS categorie
             FROM formation f LEFT JOIN categorieformation cf ON f.categorie_id = cf.id
             WHERE f.id = ?",
            [$id]
        );

        $modules = $conn->fetchAllAssociative(
            "SELECT m.titre, m.description, m.type_contenu, m.duree_minutes, m.ordre,
                    COALESCE(m.contenu_texte,'') AS contenu_texte,
                    COALESCE(mp.statut,'not_started') AS statut,
                    COALESCE(mp.score_quiz,0) AS score
             FROM module m
             LEFT JOIN module_progression mp ON mp.module_id = m.id AND mp.employe_id = ?
             WHERE m.formation_id = ?
             ORDER BY m.ordre ASC",
            [$currentUserId, $id]
        );

        $done     = count(array_filter($modules, fn($m) => $m['statut'] === 'completed'));
        $total    = count($modules);
        $progress = $total > 0 ? (int)round($done * 100 / $total) : 0;

        $html = $this->renderView('competence/lecons/pdf/pdf_module_report.html.twig', [
            'formation' => $formation,
            'modules'   => $modules,
            'progress'  => $progress,
            'done'      => $done,
            'total'     => $total,
            'date'      => date('d/m/Y'),
        ]);

        if (class_exists(\Dompdf\Dompdf::class)) {
            $dompdf = new \Dompdf\Dompdf(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $filename = 'rapport_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $formation['titre']) . '.pdf';
            return new Response(
                $dompdf->output(),
                200,
                [
                    'Content-Type'        => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                ]
            );
        }

        return new Response($html, 200, ['Content-Type' => 'text/html']);
    }

    // ══════════════════════════════════════════════════════
    //  HELPER PRIVÉ — Appel Groq API
    // ══════════════════════════════════════════════════════
    private function callGroq(string $apiKey, string $prompt): string
    {
        if (empty($apiKey)) throw new \RuntimeException("Clé vide.");

        $payload = json_encode([
            'model'      => 'llama-3.3-70b-versatile',
            'max_tokens' => 1024,
            'messages'   => [
                ['role' => 'system', 'content' => 'Tu es un assistant RH expert en formation et développement des compétences. Réponds toujours en français.'],
                ['role' => 'user',   'content' => $prompt],
            ],
        ]);

        $ch = curl_init(self::$GROQ_URL);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_TIMEOUT        => 45,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
            ],
        ]);

        $body    = curl_exec($ch);
        $code    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);

        if ($curlErr) throw new \RuntimeException("cURL error: $curlErr");
        if ($code !== 200) throw new \RuntimeException("Groq API error $code : " . substr($body, 0, 200));

        $data    = json_decode($body, true);
        $content = $data['choices'][0]['message']['content'] ?? null;
        if ($content === null) throw new \RuntimeException("Réponse Groq invalide");

        return $content;
    }
}
