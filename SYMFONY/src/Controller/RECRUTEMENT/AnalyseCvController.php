<?php

namespace App\Controller\RECRUTEMENT;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/analyse-cv', name: 'app_analyse_cv')]
class AnalyseCvController extends AbstractController
{
    private const WEBHOOK_URL = 'https://bouassida003.app.n8n.cloud/webhook-test/cv-upload';

    // ── Page principale ──────────────────────────────────────────────────
    #[Route('', name: '_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('RECRUTEMENT/analyse_cv/index.html.twig');
    }

    // ── Endpoint AJAX : reçoit le PDF → n8n → retourne JSON ─────────────
    #[Route('/analyser', name: '_analyser', methods: ['POST'])]
    public function analyser(Request $request): JsonResponse
    {
        $file = $request->files->get('cv');

        if (!$file) {
            return $this->json(['error' => 'Aucun fichier reçu.'], 400);
        }
        if ($file->getClientOriginalExtension() !== 'pdf') {
            return $this->json(['error' => 'Seuls les fichiers PDF sont acceptés.'], 400);
        }

        try {
            $json          = $this->postToN8n($file->getPathname(), $file->getClientOriginalName());
            $vote          = $this->parseVote($json);
            $consideration = $this->parseConsideration($json);
            $axisScores    = $this->deriveAxisScores($consideration, $vote);

            return $this->json([
                'vote'          => $vote,
                'consideration' => $consideration,
                'axisScores'    => $axisScores,
                'mention'       => $this->getMention($vote),
                'color'         => $this->getColor($vote),
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    // ── HTTP multipart POST vers le webhook n8n ──────────────────────────
    private function postToN8n(string $filePath, string $fileName): string
    {
        $boundary = '----FormBoundary' . bin2hex(random_bytes(16));
        $crlf     = "\r\n";

        $body  = '--' . $boundary . $crlf;
        $body .= 'Content-Disposition: form-data; name="cv"; filename="' . $fileName . '"' . $crlf;
        $body .= 'Content-Type: application/pdf' . $crlf . $crlf;
        $body .= file_get_contents($filePath);
        $body .= $crlf . '--' . $boundary . '--' . $crlf;

        $context = stream_context_create([
            'http' => [
                'method'        => 'POST',
                'header'        => [
                    'Content-Type: multipart/form-data; boundary=' . $boundary,
                    'Content-Length: ' . strlen($body),
                ],
                'content'       => $body,
                'timeout'       => 120,
                'ignore_errors' => true,
            ],
        ]);

        $response = @file_get_contents(self::WEBHOOK_URL, false, $context);

        if ($response === false) {
            throw new \RuntimeException('Impossible de contacter le webhook n8n. Vérifiez la connexion.');
        }

        $statusLine = $http_response_header[0] ?? '';
        if (preg_match('/\s(\d{3})\s/', $statusLine, $m) && (int)$m[1] >= 400) {
            throw new \RuntimeException('Erreur webhook HTTP ' . $m[1]);
        }

        return trim($response);
    }

    // ── Parseurs JSON ────────────────────────────────────────────────────
    private function parseVote(string $json): float
    {
        $decoded = json_decode($json, true);
        if (is_array($decoded)) {
            $item = isset($decoded[0]) ? $decoded[0] : $decoded;
            if (isset($item['output']['vote'])) {
                return (float) $item['output']['vote'];
            }
            if (isset($item['vote'])) {
                return (float) $item['vote'];
            }
        }

        if (preg_match('/"vote"\s*:\s*"?(\d+(?:\.\d+)?)"?/', $json, $m)) {
            return (float) $m[1];
        }

        return 5.0;
    }

    private function parseConsideration(string $json): string
    {
        $decoded = json_decode($json, true);
        if (is_array($decoded)) {
            $item = isset($decoded[0]) ? $decoded[0] : $decoded;
            if (!empty($item['output']['consideration'])) {
                return $item['output']['consideration'];
            }
            if (!empty($item['consideration'])) {
                return $item['consideration'];
            }
        }

        if (preg_match('/"consideration"\s*:\s*"((?:[^"\\\\]|\\\\.)*)"/', $json, $m)) {
            return str_replace(['\n', '\"'], ["\n", '"'], $m[1]);
        }

        return 'Aucune évaluation disponible.';
    }

    // ── Dérivation des scores par axe ────────────────────────────────────
    /** @return float[] */
    private function deriveAxisScores(string $text, float $vote): array
    {
        $lo = strtolower($text);
        $s  = array_fill(0, 5, $vote);

        $s[0] = $this->has($lo, ['python', 'java', 'javascript', 'php', 'technical', 'stack', 'framework'])
            ? ($this->has($lo, ["doesn't", 'lacks', 'missing', 'not mentioned']) ? $vote * 0.7 : $vote * 1.1)
            : $vote;

        $s[1] = $this->has($lo, ['experience', 'years', 'junior', 'senior', 'background'])
            ? ($this->has($lo, ['lacking', 'insufficient', 'limited', "doesn't fully"]) ? $vote * 0.75 : $vote * 1.05)
            : $vote;

        $s[2] = $this->has($lo, ['requirement', 'profile', 'match', 'meet', 'fit'])
            ? ($this->has($lo, ["doesn't fully", 'not fully', 'partially', 'some']) ? $vote * 0.8 : $vote * 1.1)
            : $vote;

        $s[3] = $this->has($lo, ['location', 'tunis', 'remote', 'local', 'relocation', 'plus'])
            ? ($this->has($lo, ['plus', 'advantage', 'great', 'perfect']) ? min(10, $vote * 1.3) : $vote * 0.8)
            : $vote;

        $s[4] = $this->has($lo, ['potential', 'learn', 'grow', 'promising', 'eager', 'motivated'])
            ? ($this->has($lo, ['potential']) ? $vote * 1.1 : $vote * 0.9)
            : $vote;

        return array_map(fn($v) => round(max(1.0, min(10.0, $v)), 1), $s);
    }

    /** @param string[] $keywords */
    private function has(string $text, array $keywords): bool
    {
        foreach ($keywords as $k) {
            if (str_contains($text, $k)) return true;
        }
        return false;
    }

    // ── Mention et couleur du score ──────────────────────────────────────
    private function getMention(float $vote): string
    {
        return match (true) {
            $vote >= 8 => 'Excellent profil ✅',
            $vote >= 6 => 'Bon profil 👍',
            $vote >= 4 => 'Profil moyen ⚠️',
            default    => 'Profil insuffisant ❌',
        };
    }

    private function getColor(float $vote): string
    {
        return match (true) {
            $vote >= 7 => '#27ae60',
            $vote >= 4 => '#f39c12',
            default    => '#e74c3c',
        };
    }
}