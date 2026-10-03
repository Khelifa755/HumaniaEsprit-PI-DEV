<?php

namespace App\Controller\COMPETENCE;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/competence')]
class CompetenceController extends AbstractController
{
    private function getCurrentUserId(): int
    {
        $user = $this->getUser();
        if (!$user || !method_exists($user, 'getId') || $user->getId() === null) {
            throw $this->createAccessDeniedException('Utilisateur non authentifie.');
        }

        return (int) $user->getId();
    }

    private function denyUnlessManagerRole(): void
    {
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_RH')) {
            throw $this->createAccessDeniedException('Acces reserve aux roles RH/ADMIN.');
        }
    }

    private function denyUnlessAdminRole(): void
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Acces reserve au role ADMIN.');
        }
    }

    // ══════════════════════════════════════════════════════
    //  CATALOG — page principale (toutes les vues)
    // ══════════════════════════════════════════════════════
    #[Route('/catalog', name: 'competence_catalog', methods: ['GET'])]
    public function catalog(EntityManagerInterface $em): Response
    {
        $conn = $em->getConnection();

        $currentUserId = $this->getCurrentUserId();

        // ── Compétences du catalogue ─────────────────────────────────────
        $competences = $conn->fetchAllAssociative("
            SELECT c.id, c.libelle, c.typeCompetence, c.niveauMax, c.statutCompetence,
                   cc.id AS cat_id, cc.libelle AS cat_libelle,
                   COALESCE(cc.couleur, '#6366F1') AS cat_couleur
            FROM competence c
            LEFT JOIN categoriecompetence cc ON c.categorie_id = cc.id
            ORDER BY cc.libelle, c.libelle
            LIMIT 99
        ");

        $categories = $conn->fetchAllAssociative(
            "SELECT id, libelle, COALESCE(couleur,'#6366F1') AS couleur FROM categoriecompetence ORDER BY libelle LIMIT 99"
        );

        // ── Stats catalogue ──────────────────────────────────────────────
        $total     = count($competences);
        $totalCats = count($categories);
        $critiques = count(array_filter($competences, fn($c) => $c['typeCompetence'] === 'CRITIQUE'));
        $avgMax    = $total > 0
            ? round(array_sum(array_column($competences, 'niveauMax')) / $total, 1)
            : 0;

        // ── "Mes Compétences" — données pour la vue employé ─────────────
        // Toutes les compétences avec le niveau de l'utilisateur courant
        $mySkills = $conn->fetchAllAssociative("
            SELECT c.id, c.libelle, c.typeCompetence, c.niveauMax, c.statutCompetence,
                   cc.id AS cat_id, cc.libelle AS cat_libelle,
                   COALESCE(cc.couleur, '#6366F1') AS cat_couleur,
                   ce.niveauActuel, ce.niveauValide, ce.preuveUrl, ce.dateEvaluation
            FROM competence c
            LEFT JOIN categoriecompetence cc ON c.categorie_id = cc.id
            LEFT JOIN competenceemploye ce
                ON ce.competence_id = c.id AND ce.employe_id = ?
            WHERE c.statutCompetence = 'ACTIF'
            ORDER BY cc.libelle, c.libelle
            LIMIT 99
        ", [$currentUserId]);

        // ── Stats "Mes Compétences" ──────────────────────────────────────
        $myTotal      = count($mySkills);
        $myEvaluated  = count(array_filter($mySkills, fn($s) => $s['niveauActuel'] !== null));
        $myMaitrises  = count(array_filter($mySkills, fn($s) => $s['niveauActuel'] !== null && (int)$s['niveauActuel'] >= (int)$s['niveauMax']));
        $myInProgress = count(array_filter($mySkills, fn($s) => $s['niveauActuel'] !== null && (int)$s['niveauActuel'] > 0 && (int)$s['niveauActuel'] < (int)$s['niveauMax']));
        $myGaps       = count(array_filter($mySkills, fn($s) => $s['niveauActuel'] !== null && (int)$s['niveauActuel'] < round($s['niveauMax'] * 0.6)));
        $myScore      = $myEvaluated > 0
            ? round(array_sum(array_map(fn($s) => $s['niveauActuel'] !== null ? (int)$s['niveauActuel'] / max(1, (int)$s['niveauMax']) * 100 : 0, $mySkills)) / $myTotal)
            : 0;

        return $this->render('competence/competences/competence_index.html.twig', [
            'competences' => $competences,
            'categories'  => $categories,
            'stats'       => compact('total', 'totalCats', 'critiques', 'avgMax'),
            // Mes Compétences
            'mySkills'    => $mySkills,
            'myStats'     => [
                'maitrises'  => $myMaitrises,
                'inProgress' => $myInProgress,
                'gaps'       => $myGaps,
                'score'      => $myScore,
                'total'      => $myTotal,
            ],
        ]);
    }

    // ══════════════════════════════════════════════════════
    //  DECLARE LEVEL — sauvegarder le niveau déclaré (AJAX)
    // ══════════════════════════════════════════════════════
    #[Route('/{id}/declare-level', name: 'competence_declare_level', methods: ['POST'])]
    public function declareLevel(int $id, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $conn          = $em->getConnection();
        $currentUserId = $this->getCurrentUserId();

        $niveau    = max(0, min(10, (int) $request->request->get('niveau', 0)));
        $preuveUrl = trim($request->request->get('preuveUrl', '')) ?: null;

        try {
            if ($niveau === 0) {
                // Supprimer la déclaration
                $conn->executeStatement(
                    "DELETE FROM competenceemploye WHERE employe_id = ? AND competence_id = ?",
                    [$currentUserId, $id]
                );
            } else {
                $conn->executeStatement("
                    INSERT INTO competenceemploye
                        (employe_id, competence_id, niveauActuel, niveauValide, preuveUrl, dateEvaluation)
                    VALUES (?, ?, ?, 0, ?, CURDATE())
                    ON DUPLICATE KEY UPDATE
                        niveauActuel   = VALUES(niveauActuel),
                        preuveUrl      = VALUES(preuveUrl),
                        dateEvaluation = CURDATE()
                ", [$currentUserId, $id, $niveau, $preuveUrl]);
            }

            // Recalcul stats pour retour JSON
            $niveauMax = (int)($conn->fetchOne("SELECT niveauMax FROM competence WHERE id = ?", [$id]) ?: 5);
            $isGap     = $niveau > 0 && $niveau < round($niveauMax * 0.6);

            return new JsonResponse([
                'ok'        => true,
                'niveau'    => $niveau,
                'niveauMax' => $niveauMax,
                'isGap'     => $isGap,
                'deleted'   => $niveau === 0,
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // ══════════════════════════════════════════════════════
    //  NEW — ajout d'une compétence
    // ══════════════════════════════════════════════════════
    #[Route('/new', name: 'competence_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyUnlessAdminRole();
        $conn = $em->getConnection();
        $r    = $request->request;

        $libelle = trim((string) $r->get('libelle', ''));
        $type      = $r->get('typeCompetence', 'IMPORTANTE');
        $niveauMax = max(1, min(10, (int)$r->get('niveauMax', 5)));
        $catId     = $r->get('categorie_id') ?: null;

        if (strlen($libelle) < 2) {
            $this->addFlash('error', '⚠️ Le libellé doit contenir au moins 2 caractères.');
            return $this->redirectToRoute('competence_catalog');
        }

        $conn->executeStatement("
            INSERT INTO competence (libelle, typeCompetence, niveauMax, statutCompetence, categorie_id)
            VALUES (?, ?, ?, 'ACTIF', ?)
        ", [$libelle, $type, $niveauMax, $catId]);

        $this->addFlash('success', '✅ Compétence « ' . $libelle . ' » ajoutée !');
        return $this->redirectToRoute('competence_catalog');
    }

    // ══════════════════════════════════════════════════════
    //  EDIT — modification d'une compétence
    // ══════════════════════════════════════════════════════
    #[Route('/{id}/edit', name: 'competence_edit', methods: ['POST'])]
    public function edit(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyUnlessManagerRole();
        $conn = $em->getConnection();
        $r    = $request->request;

        $libelle = trim((string) $r->get('libelle', ''));
        $type      = $r->get('typeCompetence', 'IMPORTANTE');
        $niveauMax = max(1, min(10, (int)$r->get('niveauMax', 5)));
        $catId     = $r->get('categorie_id') ?: null;

        if (strlen($libelle) < 2) {
            $this->addFlash('error', '⚠️ Le libellé doit contenir au moins 2 caractères.');
            return $this->redirectToRoute('competence_catalog');
        }

        $conn->executeStatement("
            UPDATE competence
            SET libelle = ?, typeCompetence = ?, niveauMax = ?, categorie_id = ?
            WHERE id = ?
        ", [$libelle, $type, $niveauMax, $catId, $id]);

        $this->addFlash('success', '✅ Compétence mise à jour !');
        return $this->redirectToRoute('competence_catalog');
    }

    // ══════════════════════════════════════════════════════
    //  TOGGLE — activer / désactiver
    // ══════════════════════════════════════════════════════
    #[Route('/{id}/toggle', name: 'competence_toggle', methods: ['POST'])]
    public function toggle(int $id, EntityManagerInterface $em): Response
    {
        $this->denyUnlessManagerRole();
        $conn    = $em->getConnection();
        $current = $conn->fetchOne("SELECT statutCompetence FROM competence WHERE id = ?", [$id]);
        $new     = $current === 'ACTIF' ? 'INACTIF' : 'ACTIF';

        $conn->executeStatement("UPDATE competence SET statutCompetence = ? WHERE id = ?", [$new, $id]);
        $this->addFlash('success', '✅ Statut mis à jour.');
        return $this->redirectToRoute('competence_catalog');
    }

    // ══════════════════════════════════════════════════════
    //  DELETE
    // ══════════════════════════════════════════════════════
    #[Route('/{id}/delete', name: 'competence_delete', methods: ['POST'])]
    public function delete(int $id, EntityManagerInterface $em): Response
    {
        $this->denyUnlessManagerRole();
        $conn   = $em->getConnection();
        $nbUsed = (int)($conn->fetchOne(
            "SELECT COUNT(*) FROM competenceemploye WHERE competence_id = ?", [$id]
        ) ?: 0);

        if ($nbUsed > 0) {
            $this->addFlash('error', "⚠️ Impossible : cette compétence est assignée à $nbUsed employé(s).");
        } else {
            $conn->executeStatement("DELETE FROM competence WHERE id = ?", [$id]);
            $this->addFlash('success', '✅ Compétence supprimée.');
        }
        return $this->redirectToRoute('competence_catalog');
    }
}