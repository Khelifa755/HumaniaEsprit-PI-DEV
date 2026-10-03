<?php

namespace App\Controller\COMPETENCE;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Équivalent web de ModuleLearnerController.java
 * Gère la lecture individuelle d'un module (vidéo YouTube, lecture, quiz)
 * avec sidebar Notes / IA / Vidéos suggérées.
 */
#[Route('/mes-lecons/module')]
class ModuleLearnerController extends AbstractController
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
    //  PAGE DE LECTURE D'UN MODULE
    // ══════════════════════════════════════════════════════
    #[Route('/{id}', name: 'module_learner_show', methods: ['GET'])]
    public function show(int $id, EntityManagerInterface $em): Response
    {
        $conn          = $em->getConnection();
        $currentUserId = $this->getCurrentUserId();

        // ── Module principal ──────────────────────────────────────────────
        $module = $conn->fetchAssociative(
            "SELECT m.id, m.titre, m.type_contenu, m.duree_minutes, m.ordre,
                    COALESCE(m.contenu_texte, '') AS contenu_texte,
                    COALESCE(m.url_ressource, '') AS url_ressource,
                    m.formation_id,
                    COALESCE(mp.statut, 'not_started') AS statut,
                    COALESCE(mp.score_quiz, 0) AS score_quiz
             FROM module m
             LEFT JOIN module_progression mp
                ON mp.module_id = m.id AND mp.employe_id = ?
             WHERE m.id = ?",
            [$currentUserId, $id]
        );

        if (!$module) {
            throw $this->createNotFoundException("Module #$id introuvable.");
        }

        // ── Tous les modules de la formation (navigation Précédent/Suivant) ──
        $allModules = $conn->fetchAllAssociative(
            "SELECT m.id, m.titre, m.type_contenu, m.ordre,
                    COALESCE(mp.statut, 'not_started') AS statut
             FROM module m
             LEFT JOIN module_progression mp
                ON mp.module_id = m.id AND mp.employe_id = ?
             WHERE m.formation_id = ?
             ORDER BY m.ordre ASC",
            [$currentUserId, $module['formation_id']]
        );

        // Position courante dans la liste
        $currentIndex = 0;
        foreach ($allModules as $i => $m) {
            if ($m['id'] === $id) {
                $currentIndex = $i;
                break;
            }
        }

        // ── Inscription ───────────────────────────────────────────────────
        $inscription = $conn->fetchAssociative(
            "SELECT inf.id FROM inscriptionformation inf
             JOIN sessionformation sf ON inf.session_id = sf.id
             WHERE sf.formation_id = ? AND inf.employe_id = ? LIMIT 1",
            [$module['formation_id'], $currentUserId]
        );

        // ── Notes existantes ──────────────────────────────────────────────
        $notes = [];
        try {
            $notes = $conn->fetchAllAssociative(
                "SELECT id, contenu, created_at FROM module_note
                 WHERE module_id = ? AND employe_id = ?
                 ORDER BY created_at DESC",
                [$id, $currentUserId]
            );
        } catch (\Exception $e) { /* table optionnelle */
        }

        // ── Sections (sous-sections) ──────────────────────────────────────
        $sections = [];
        try {
            $sections = $conn->fetchAllAssociative(
                "SELECT ms.id, ms.titre, ms.description,
                        COALESCE(ssp.statut, 'not_started') AS statut
                 FROM module_section ms
                 LEFT JOIN sous_section_progression ssp
                    ON ssp.sous_section_id = ms.id AND ssp.employe_id = ?
                 WHERE ms.module_id = ?
                 ORDER BY ms.id ASC",
                [$currentUserId, $id]
            );
        } catch (\Exception $e) { /* table optionnelle */
        }

        // ── Questions quiz (si type = quiz) ──────────────────────────────
        $quizQuestions = [];
        if (in_array(strtolower($module['type_contenu']), ['quiz'])) {
            try {
                $quizQuestions = $conn->fetchAllAssociative(
                    "SELECT id, question, option_a, option_b, option_c, option_d
                     FROM quiz_question WHERE module_id = ? ORDER BY id ASC",
                    [$id]
                );
            } catch (\Exception $e) { /* table optionnelle */
            }
        }

        // ── Progression globale de la formation ───────────────────────────
        $totalMods = count($allModules);
        $doneMods  = count(array_filter($allModules, fn($m) => $m['statut'] === 'completed'));
        $progress  = $totalMods > 0 ? (int) round($doneMods * 100 / $totalMods) : 0;

        // ── Conversion URL vidéo → embed YouTube ─────────────────────────
        $embedUrl = $this->toYoutubeEmbed($module['url_ressource']);

        return $this->render('competence/lecons/module_learner.html.twig', [
            'module'        => $module,
            'embedUrl'      => $embedUrl,
            'allModules'    => $allModules,
            'currentIndex'  => $currentIndex,
            'prevModule'    => $currentIndex > 0 ? $allModules[$currentIndex - 1] : null,
            'nextModule'    => $currentIndex < ($totalMods - 1) ? $allModules[$currentIndex + 1] : null,
            'inscription'   => $inscription,
            'notes'         => $notes,
            'sections'      => $sections,
            'quizQuestions' => $quizQuestions,
            'progress'      => $progress,
            'doneMods'      => $doneMods,
            'totalMods'     => $totalMods,
            'userId'        => $currentUserId,
            'isCompleted'   => $module['statut'] === 'completed',
        ]);
    }

    // ══════════════════════════════════════════════════════
    //  SAVE NOTE (AJAX)
    // ══════════════════════════════════════════════════════
    #[Route('/{id}/note', name: 'module_learner_save_note', methods: ['POST'])]
    public function saveNote(int $id, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $conn          = $em->getConnection();
        $currentUserId = $this->getCurrentUserId();
        $contenu       = trim($request->request->get('contenu', ''));

        if (empty($contenu)) {
            return new JsonResponse(['ok' => false, 'error' => 'Note vide'], 400);
        }

        try {
            $conn->executeStatement(
                "CREATE TABLE IF NOT EXISTS module_note (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    module_id INT NOT NULL,
                    employe_id INT NOT NULL,
                    contenu TEXT NOT NULL,
                    created_at DATETIME DEFAULT NOW(),
                    INDEX idx_mn (module_id, employe_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
            );

            $conn->executeStatement(
                "INSERT INTO module_note (module_id, employe_id, contenu, created_at)
                 VALUES (?, ?, ?, NOW())",
                [$id, $currentUserId, $contenu]
            );

            $noteId = $conn->lastInsertId();
            return new JsonResponse([
                'ok'         => true,
                'note_id'    => $noteId,
                'created_at' => date('d/m/Y H:i'),
                'contenu'    => $contenu,
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // ══════════════════════════════════════════════════════
    //  DELETE NOTE (AJAX)
    // ══════════════════════════════════════════════════════
    #[Route('/{id}/note/{noteId}/delete', name: 'module_learner_delete_note', methods: ['POST'])]
    public function deleteNote(int $id, int $noteId, EntityManagerInterface $em): JsonResponse
    {
        $conn          = $em->getConnection();
        $currentUserId = $this->getCurrentUserId();

        try {
            $affected = $conn->executeStatement(
                "DELETE FROM module_note WHERE id = ? AND module_id = ? AND employe_id = ?",
                [$noteId, $id, $currentUserId]
            );

            if ($affected === 0) {
                return new JsonResponse(['ok' => false, 'error' => 'Note introuvable'], 404);
            }
            return new JsonResponse(['ok' => true]);
        } catch (\Exception $e) {
            return new JsonResponse(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // ══════════════════════════════════════════════════════
    //  GROQ AI — Résumé / Explication / Quiz
    // ══════════════════════════════════════════════════════
    #[Route('/{id}/ai', name: 'module_learner_ai', methods: ['POST'])]
    public function aiAction(int $id, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $conn          = $em->getConnection();
        $action        = $request->request->get('action', 'summary');
        $currentUserId = $this->getCurrentUserId();

        $module = $conn->fetchAssociative(
            "SELECT titre, contenu_texte, type_contenu FROM module WHERE id = ?",
            [$id]
        );
        if (!$module) return new JsonResponse(['ok' => false, 'error' => 'Module introuvable'], 404);

        $contenu = $module['contenu_texte'] ?? '';
        $titre   = $module['titre'] ?? '';

        $prompt = match ($action) {
            'summary' => "Résume ce cours en français de façon concise et structurée avec des points clés.\n\nTitre : $titre\n\nContenu :\n$contenu",
            'explain' => "Explique les concepts difficiles de ce cours en termes simples et accessibles.\n\nTitre : $titre\n\nContenu :\n$contenu",
            'quiz'    => "Génère 3 questions QCM (A/B/C/D) en français avec la réponse correcte.\nFormat JSON strict uniquement, sans texte avant ni après :\n[{\"question\":\"...\",\"options\":{\"A\":\"...\",\"B\":\"...\",\"C\":\"...\",\"D\":\"...\"},\"answer\":\"A\"}]\n\nCours : $titre\n\n$contenu",
            default   => "Résume ce cours : $titre\n\n$contenu",
        };

        $groqKey = $this->getParameter('groq_api_key');

        try {
            $response = $this->callGroq($groqKey, $prompt);
            return new JsonResponse(['ok' => true, 'result' => $response, 'action' => $action]);
        } catch (\Exception $e) {
            return new JsonResponse(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // ══════════════════════════════════════════════════════
    //  YOUTUBE SUGGESTIONS (AJAX)
    // ══════════════════════════════════════════════════════
    #[Route('/{id}/youtube', name: 'module_learner_youtube', methods: ['GET'])]
    public function youtubeSuggestions(int $id, EntityManagerInterface $em): JsonResponse
    {
        $conn   = $em->getConnection();
        $module = $conn->fetchAssociative("SELECT titre FROM module WHERE id = ?", [$id]);
        if (!$module) return new JsonResponse(['ok' => false, 'error' => 'Module introuvable'], 404);

        $youtubeKey = $this->getParameter('youtube_api_key');
        $query      = urlencode($module['titre'] . ' tutorial formation');
        $url        = "https://www.googleapis.com/youtube/v3/search?part=snippet&q={$query}&type=video&maxResults=5&relevanceLanguage=fr&key={$youtubeKey}";

        try {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 10,
                CURLOPT_SSL_VERIFYPEER => false,  // ← ajouter
                CURLOPT_SSL_VERIFYHOST => 0,  // ← ajouter
            ]);
            $body = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($code !== 200) throw new \RuntimeException("YouTube API error $code");

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
    //  HIGHLIGHT SAVE (AJAX)
    // ══════════════════════════════════════════════════════
    #[Route('/{id}/highlight', name: 'module_learner_highlight', methods: ['POST'])]
    public function saveHighlight(int $id, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $conn          = $em->getConnection();
        $currentUserId = $this->getCurrentUserId();
        $texte         = trim($request->request->get('texte', ''));
        $couleur       = $request->request->get('couleur', '#FEF08A');

        if (empty($texte)) return new JsonResponse(['ok' => false, 'error' => 'Texte vide'], 400);

        try {
            $conn->executeStatement(
                "CREATE TABLE IF NOT EXISTS module_highlight (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    module_id INT NOT NULL,
                    employe_id INT NOT NULL,
                    texte TEXT NOT NULL,
                    couleur VARCHAR(20) DEFAULT '#FEF08A',
                    created_at DATETIME DEFAULT NOW(),
                    INDEX idx_mh (module_id, employe_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
            );

            $conn->executeStatement(
                "INSERT INTO module_highlight (module_id, employe_id, texte, couleur, created_at)
                 VALUES (?, ?, ?, ?, NOW())",
                [$id, $currentUserId, $texte, $couleur]
            );

            return new JsonResponse(['ok' => true]);
        } catch (\Exception $e) {
            return new JsonResponse(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // ══════════════════════════════════════════════════════
    //  HELPERS PRIVÉS
    // ══════════════════════════════════════════════════════
    private function toYoutubeEmbed(string $url): ?string
    {
        if (empty($url)) return null;
        if (str_contains($url, 'youtube.com/embed/')) return $url;
        if (preg_match('~youtu\.be/([a-zA-Z0-9_-]{11})~', $url, $m)) {
            return 'https://www.youtube.com/embed/' . $m[1];
        }
        if (preg_match('~[?&]v=([a-zA-Z0-9_-]{11})~', $url, $m)) {
            return 'https://www.youtube.com/embed/' . $m[1];
        }
        return null;
    }

    // ══════════════════════════════════════════════════════
    //  SUBMIT EXERCICE — correction IA (AJAX POST)
    // ══════════════════════════════════════════════════════
    #[Route('/{id}/exercice', name: 'module_learner_exercice', methods: ['POST'])]
    public function submitExercice(int $id, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $conn          = $em->getConnection();
        $currentUserId = $this->getCurrentUserId();

        $reponse = trim($request->request->get('reponse', ''));
        if (strlen($reponse) < 5) {
            return new JsonResponse(['ok' => false, 'error' => 'Réponse trop courte (min. 5 caractères).'], 400);
        }

        $module = $conn->fetchAssociative(
            "SELECT titre, contenu_texte, type_contenu FROM module WHERE id = ?", [$id]
        );
        if (!$module) {
            return new JsonResponse(['ok' => false, 'error' => 'Module introuvable.'], 404);
        }

        $enonce = $module['contenu_texte'] ?? '';
        $titre  = $module['titre'] ?? '';

        $prompt = "Tu es un correcteur pédagogique expert. Évalue la réponse d'un étudiant à l'exercice suivant.

Titre du module : $titre

Énoncé de l'exercice :
$enonce

Réponse de l'étudiant :
$reponse

Donne une évaluation structurée en JSON strict UNIQUEMENT (sans texte avant ni après, sans backticks) avec ces champs :
{
  \"score\": <entier de 0 à 100>,
  \"niveau\": <\"Excellent\" | \"Bien\" | \"Passable\" | \"Insuffisant\">,
  \"correct\": <true si score >= 60, false sinon>,
  \"points_forts\": [<liste de 1 à 3 points forts en français>],
  \"points_ameliorer\": [<liste de 1 à 3 axes d'amélioration en français>],
  \"correction\": \"<correction détaillée et pédagogique en français, 2-4 phrases>\",
  \"conseil\": \"<un conseil actionnable pour progresser, 1 phrase>\"
}";

        try {
            $groqKey = $this->getParameter('groq_api_key');
            $raw     = $this->callGroq($groqKey, $prompt);

            // Nettoyer et parser le JSON
            $clean = preg_replace('/^```json\s*/i', '', trim($raw));
            $clean = preg_replace('/```\s*$/i', '', $clean);
            $data  = json_decode(trim($clean), true);

            if (!$data || !isset($data['score'])) {
                // Fallback si le JSON est invalide
                return new JsonResponse([
                    'ok'      => true,
                    'score'   => 0,
                    'niveau'  => 'Inconnu',
                    'correct' => false,
                    'points_forts'        => [],
                    'points_ameliorer'    => [],
                    'correction'          => $raw,
                    'conseil'             => '',
                    'raw'                 => true,
                ]);
            }

            // Sauvegarder la réponse dans une note si score >= 60
            if ((int)$data['score'] >= 60) {
                try {
                    $conn->executeStatement(
                        "CREATE TABLE IF NOT EXISTS module_note (
                            id INT AUTO_INCREMENT PRIMARY KEY,
                            module_id INT NOT NULL,
                            employe_id INT NOT NULL,
                            contenu TEXT NOT NULL,
                            created_at DATETIME DEFAULT NOW(),
                            INDEX idx_mn (module_id, employe_id)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
                    );
                    $noteContent = "✏️ Exercice complété — Score : {$data['score']}/100\n\nMa réponse :\n$reponse\n\nCorrection IA :\n{$data['correction']}";
                    $conn->executeStatement(
                        "INSERT INTO module_note (module_id, employe_id, contenu, created_at) VALUES (?, ?, ?, NOW())",
                        [$id, $currentUserId, $noteContent]
                    );
                } catch (\Exception $e) { /* non bloquant */ }
            }

            return new JsonResponse(array_merge(['ok' => true], $data));

        } catch (\Exception $e) {
            return new JsonResponse(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

    private function callGroq(string $apiKey, string $prompt): string
    {
        if (empty($apiKey)) throw new \RuntimeException("Clé Groq non configurée dans services.yaml");

        $payload = json_encode([
            'model' => 'llama-3.3-70b-versatile',
            'max_tokens' => 1024,
            'messages'   => [
                ['role' => 'system', 'content' => 'Tu es un assistant pédagogique expert. Réponds toujours en français sauf si demandé autrement.'],
                ['role' => 'user',   'content' => $prompt],
            ],
        ]);

        $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => false,  // ← ajouter
            CURLOPT_SSL_VERIFYHOST => 0,  // ← ajouter
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
            ],
        ]);

        $body     = curl_exec($ch);
        $code     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($curlErr) throw new \RuntimeException("cURL error: $curlErr");
        if ($code !== 200) throw new \RuntimeException("Groq API error $code : " . substr($body, 0, 300));

        $data    = json_decode($body, true);
        $content = $data['choices'][0]['message']['content'] ?? null;
        if ($content === null) throw new \RuntimeException("Réponse Groq invalide");

        return $content;
    }
}