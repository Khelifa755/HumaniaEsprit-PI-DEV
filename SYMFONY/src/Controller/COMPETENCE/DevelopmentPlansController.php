<?php

namespace App\Controller\COMPETENCE;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/pdi')]
class DevelopmentPlansController extends AbstractController
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
    //  INDEX
    // ══════════════════════════════════════════════════════
    #[Route('', name: 'pdi_index', methods: ['GET'])]
    public function index(EntityManagerInterface $em): Response
    {
        $conn = $em->getConnection();
        $currentUserId   = $this->getCurrentUserId();
        $currentUserRole = $this->getCurrentUserRoleName();

        // ── Sync formation→actions avant affichage ────────────────────────
        $this->syncFormationActions($conn, $currentUserId);

        // ── PDI selon le rôle ─────────────────────────────────────────────
        if (in_array($currentUserRole, ['ADMIN', 'RH'])) {
            $pdis = $conn->fetchAllAssociative("
                SELECT p.id, p.annee, p.progressionGlobale, p.dateCreation, p.statut,
                       IFNULL(CONCAT(u.prenom,' ',u.nom), IFNULL(u.username,'Employé')) AS employe_nom,
                       u.id AS employe_id
                FROM pdi p
                LEFT JOIN utilisateur u ON p.employe_id = u.id
                ORDER BY p.annee DESC, p.dateCreation DESC
            ");
        } elseif ($currentUserRole === 'MANAGER') {
            $pdis = $conn->fetchAllAssociative("
                SELECT p.id, p.annee, p.progressionGlobale, p.dateCreation, p.statut,
                       IFNULL(CONCAT(u.prenom,' ',u.nom), IFNULL(u.username,'Employé')) AS employe_nom,
                       u.id AS employe_id
                FROM pdi p
                LEFT JOIN utilisateur u ON p.employe_id = u.id
                WHERE u.manager_id = ? AND u.role = 'EMPLOYE'
                ORDER BY p.annee DESC, p.dateCreation DESC
            ", [$currentUserId]);
        } else {
            $pdis = $conn->fetchAllAssociative("
                SELECT p.id, p.annee, p.progressionGlobale, p.dateCreation, p.statut,
                       IFNULL(CONCAT(u.prenom,' ',u.nom), IFNULL(u.username,'Moi')) AS employe_nom,
                       u.id AS employe_id
                FROM pdi p
                LEFT JOIN utilisateur u ON p.employe_id = u.id
                WHERE p.employe_id = ?
                ORDER BY p.annee DESC, p.dateCreation DESC
            ", [$currentUserId]);
        }

        // ── Charger les actions + recalcul progression ────────────────────
        $today = new \DateTime();
        foreach ($pdis as &$pdi) {
            // ✅ FIX: use camelCase column names matching the DB schema
            $actions = $conn->fetchAllAssociative("
                SELECT id, typeAction, statut, dateDebut, dateFinPrevue, priorite,
                       IFNULL(formation_id, 0) AS formation_id
                FROM actionpdi
                WHERE pdi_id = ?
                ORDER BY priorite DESC, dateFinPrevue ASC
            ", [$pdi['id']]);

            // Calcul retard
            foreach ($actions as &$a) {
                $a['late']      = false;
                $a['days_late'] = 0;
                if ($a['dateFinPrevue'] && $a['statut'] !== 'Completed') {
                    $fin = new \DateTime($a['dateFinPrevue']);
                    if ($fin < $today) {
                        $a['late']      = true;
                        $a['days_late'] = (int)$today->diff($fin)->days;
                    }
                }
            }
            unset($a);

            $pdi['actions'] = $actions;
            $pdi['todo']    = array_values(array_filter($actions, fn($a) => $a['statut'] === 'Pending'));
            $pdi['in_prog'] = array_values(array_filter($actions, fn($a) => $a['statut'] === 'In Progress'));
            $pdi['done']    = array_values(array_filter($actions, fn($a) => $a['statut'] === 'Completed'));

            // Progression dynamique
            $total = count($actions);
            $done  = count(array_filter($actions, fn($a) => $a['statut'] === 'Completed'));
            $pdi['pct'] = $total > 0 ? (int)round($done * 100 / $total) : 0;

            if ($pdi['pct'] !== (int)$pdi['progressionGlobale']) {
                $newStatut = $pdi['pct'] >= 100 ? 'Completed' : 'Active';
                $conn->executeStatement(
                    "UPDATE pdi SET progressionGlobale = ?, statut = ? WHERE id = ?",
                    [$pdi['pct'], $newStatut, $pdi['id']]
                );
                $pdi['statut'] = $newStatut;
            }

            // Compétences cibles (labels uniques des actions)
            $pdi['comp_tags'] = array_unique(array_map(fn($a) => $a['typeAction'], $actions));
        }
        unset($pdi);

        // ── Stats globales ────────────────────────────────────────────────
        $activePlans = (int)($conn->fetchOne("SELECT COUNT(*) FROM pdi WHERE statut='Active'") ?: 0);
        // ✅ FIX: camelCase column
        $actionsDue  = (int)($conn->fetchOne(
            "SELECT COUNT(*) FROM actionpdi WHERE statut IN ('Pending','In Progress') AND dateFinPrevue <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)"
        ) ?: 0);
        $completed   = (int)($conn->fetchOne("SELECT COUNT(*) FROM actionpdi WHERE statut='Completed'") ?: 0);

        // Progression moyenne dynamique
        $avgRows = $conn->fetchAllAssociative(
            "SELECT p.id, COUNT(a.id) AS total,
                    SUM(CASE WHEN a.statut='Completed' THEN 1 ELSE 0 END) AS done
             FROM pdi p
             LEFT JOIN actionpdi a ON a.pdi_id = p.id
             WHERE p.statut = 'Active'
             GROUP BY p.id"
        );
        $avgSum = 0; $avgCount = 0;
        foreach ($avgRows as $r) {
            if ($r['total'] > 0) $avgSum += (int)round($r['done'] * 100 / $r['total']);
            $avgCount++;
        }
        $avgProgress = $avgCount > 0 ? (int)round($avgSum / $avgCount) : 0;

        // ── Employés pour formulaire création ────────────────────────────
        if (in_array($currentUserRole, ['ADMIN', 'RH'])) {
            $employees = $conn->fetchAllAssociative(
                "SELECT id, CONCAT(prenom,' ',nom) AS fullName
                 FROM utilisateur
                 WHERE role = 'EMPLOYE' AND statut = 'Actif'
                 ORDER BY nom"
            );
        } elseif ($currentUserRole === 'MANAGER') {
            $employees = $conn->fetchAllAssociative(
                "SELECT id, CONCAT(prenom,' ',nom) AS fullName
                 FROM utilisateur
                 WHERE role = 'EMPLOYE' AND statut = 'Actif' AND manager_id = ?
                 ORDER BY nom",
                [$currentUserId]
            );
        } else {
            $employees = $conn->fetchAllAssociative(
                "SELECT id, CONCAT(prenom,' ',nom) AS fullName
                 FROM utilisateur
                 WHERE id = ?
                 ORDER BY nom",
                [$currentUserId]
            );
        }

        // ── Formations pour formulaire action ────────────────────────────
        $formations = $conn->fetchAllAssociative("SELECT id, titre FROM formation ORDER BY titre");

        return $this->render('competence/pdi/pdi_index.html.twig', [
            'pdis'          => $pdis,
            'stats'         => compact('activePlans', 'avgProgress', 'actionsDue', 'completed'),
            'isAdmin'       => in_array($currentUserRole, ['ADMIN', 'RH']),
            'isManager'     => $currentUserRole === 'MANAGER',
            'currentUserId' => $currentUserId,
            'employees'     => $employees,
            'formations'    => $formations,
        ]);
    }

    // ══════════════════════════════════════════════════════
    //  CREATE PDI
    // ══════════════════════════════════════════════════════
    #[Route('/new', name: 'pdi_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $conn = $em->getConnection();
        $employeId = (int) $request->request->get('employe_id', 0);
        if ($employeId <= 0) {
            $employeId = $this->getCurrentUserId();
        }
        $annee     = (int)$request->request->get('annee', date('Y'));

        // Vérifier pas de doublon (même employé, même année)
        $exists = $conn->fetchOne("SELECT id FROM pdi WHERE employe_id = ? AND annee = ?", [$employeId, $annee]);
        if ($exists) {
            $this->addFlash('error', "⚠️ Un PDI existe déjà pour cet employé en $annee.");
            return $this->redirectToRoute('pdi_index');
        }

        $conn->executeStatement(
            "INSERT INTO pdi (employe_id, annee, progressionGlobale, dateCreation, statut) VALUES (?, ?, 0, CURDATE(), 'Draft')",
            [$employeId, $annee]
        );
        $this->addFlash('success', '✅ PDI créé avec succès !');
        return $this->redirectToRoute('pdi_index');
    }

    // ══════════════════════════════════════════════════════
    //  EDIT PDI (statut)
    // ══════════════════════════════════════════════════════
    #[Route('/{id}/edit', name: 'pdi_edit', methods: ['POST'])]
    public function edit(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $conn   = $em->getConnection();
        $statut = $request->request->get('statut', 'Active');
        $annee  = (int)$request->request->get('annee', date('Y'));

        $conn->executeStatement(
            "UPDATE pdi SET statut = ?, annee = ? WHERE id = ?",
            [$statut, $annee, $id]
        );
        $this->addFlash('success', '✅ PDI mis à jour !');
        return $this->redirectToRoute('pdi_index');
    }

    // ══════════════════════════════════════════════════════
    //  DELETE PDI
    // ══════════════════════════════════════════════════════
    #[Route('/{id}/delete', name: 'pdi_delete', methods: ['POST'])]
    public function delete(int $id, EntityManagerInterface $em): Response
    {
        $conn = $em->getConnection();
        $conn->executeStatement("DELETE FROM actionpdi WHERE pdi_id = ?", [$id]);
        $conn->executeStatement("DELETE FROM pdi WHERE id = ?", [$id]);
        $this->addFlash('success', '✅ PDI supprimé.');
        return $this->redirectToRoute('pdi_index');
    }

    // ══════════════════════════════════════════════════════
    //  ADD ACTION
    // ══════════════════════════════════════════════════════
    #[Route('/{pdiId}/action/add', name: 'pdi_action_add', methods: ['POST'])]
    public function addAction(int $pdiId, Request $request, EntityManagerInterface $em): Response
    {
        $conn = $em->getConnection();
        $r    = $request->request;

        // ── Résoudre le label de l'action ─────────────────────────────────
        // Priorité : si une formation est sélectionnée → "Formation : <titre>"
        // Sinon     : utiliser le champ texte libre (type_action)
        $formationId = $r->get('formation_id') ? (int)$r->get('formation_id') : null;
        $customLabel = trim($r->get('type_action', ''));

        if ($formationId) {
            // Récupérer le titre de la formation pour construire le label
            $titre = $conn->fetchOne("SELECT titre FROM formation WHERE id = ?", [$formationId]);
            $typeAction = $titre ? "Formation : $titre" : $customLabel;
        } else {
            $typeAction  = $customLabel;
            $formationId = null;
        }

        if (empty($typeAction)) {
            $this->addFlash('error', '⚠️ Veuillez saisir une action ou sélectionner une formation.');
            return $this->redirectToRoute('pdi_index');
        }

        // ✅ FIX: use camelCase column names + explicit column list (no id → AUTO_INCREMENT safe)
        $conn->executeStatement(
            "INSERT INTO actionpdi (pdi_id, typeAction, statut, dateDebut, dateFinPrevue, priorite, formation_id)
             VALUES (?, ?, ?, ?, ?, ?, ?)",
            [
                $pdiId,
                $typeAction,
                $r->get('statut', 'Pending'),
                $r->get('date_debut') ?: null,
                $r->get('date_fin_prevue') ?: null,
                (int)$r->get('priorite', 3),
                $formationId,
            ]
        );
        $this->recalcProgression($conn, $pdiId);
        $this->addFlash('success', '✅ Action ajoutée !');
        return $this->redirectToRoute('pdi_index');
    }

    // ══════════════════════════════════════════════════════
    //  EDIT ACTION
    // ══════════════════════════════════════════════════════
    #[Route('/action/{id}/edit', name: 'pdi_action_edit', methods: ['POST'])]
    public function editAction(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $conn  = $em->getConnection();
        $r     = $request->request;
        $pdiId = (int)$conn->fetchOne("SELECT pdi_id FROM actionpdi WHERE id = ?", [$id]);

        $formationId = $r->get('formation_id') ? (int)$r->get('formation_id') : null;
        $customLabel = trim($r->get('type_action', ''));

        if ($formationId) {
            $titre = $conn->fetchOne("SELECT titre FROM formation WHERE id = ?", [$formationId]);
            $typeAction = $titre ? "Formation : $titre" : $customLabel;
        } else {
            $typeAction  = $customLabel;
            $formationId = null;
        }

        if (empty($typeAction)) {
            $this->addFlash('error', '⚠️ Veuillez saisir une action ou sélectionner une formation.');
            return $this->redirectToRoute('pdi_index');
        }

        // ✅ FIX: camelCase columns
        $conn->executeStatement(
            "UPDATE actionpdi SET typeAction=?, statut=?, dateDebut=?, dateFinPrevue=?, priorite=?, formation_id=? WHERE id=?",
            [
                $typeAction,
                $r->get('statut'),
                $r->get('date_debut') ?: null,
                $r->get('date_fin_prevue') ?: null,
                (int)$r->get('priorite', 3),
                $formationId,
                $id,
            ]
        );
        $this->recalcProgression($conn, $pdiId);
        $this->addFlash('success', '✅ Action mise à jour !');
        return $this->redirectToRoute('pdi_index');
    }

    // ══════════════════════════════════════════════════════
    //  MOVE ACTION (Kanban drag — AJAX POST)
    // ══════════════════════════════════════════════════════
    #[Route('/action/{id}/move', name: 'pdi_action_move', methods: ['POST'])]
    public function moveAction(int $id, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $conn   = $em->getConnection();
        $statut = $request->request->get('statut');
        if (!in_array($statut, ['Pending', 'In Progress', 'Completed'])) {
            return new JsonResponse(['ok' => false, 'error' => 'Statut invalide'], 400);
        }
        $pdiId = (int)$conn->fetchOne("SELECT pdi_id FROM actionpdi WHERE id = ?", [$id]);
        $conn->executeStatement("UPDATE actionpdi SET statut = ? WHERE id = ?", [$statut, $id]);
        $this->recalcProgression($conn, $pdiId);

        $pdi = $conn->fetchAssociative("SELECT progressionGlobale, statut FROM pdi WHERE id = ?", [$pdiId]);
        return new JsonResponse(['ok' => true, 'pct' => $pdi['progressionGlobale'], 'pdiStatut' => $pdi['statut']]);
    }

    // ══════════════════════════════════════════════════════
    //  DELETE ACTION
    // ══════════════════════════════════════════════════════
    #[Route('/action/{id}/delete', name: 'pdi_action_delete', methods: ['POST'])]
    public function deleteAction(int $id, EntityManagerInterface $em): Response
    {
        $conn  = $em->getConnection();
        $pdiId = (int)$conn->fetchOne("SELECT pdi_id FROM actionpdi WHERE id = ?", [$id]);
        $conn->executeStatement("DELETE FROM actionpdi WHERE id = ?", [$id]);
        $this->recalcProgression($conn, $pdiId);
        $this->addFlash('success', '✅ Action supprimée.');
        return $this->redirectToRoute('pdi_index');
    }

    // ══════════════════════════════════════════════════════
    //  HELPERS PRIVÉS
    // ══════════════════════════════════════════════════════
    private function recalcProgression(\Doctrine\DBAL\Connection $conn, int $pdiId): void
    {
        $row = $conn->fetchAssociative(
            "SELECT COUNT(*) AS total, SUM(CASE WHEN statut='Completed' THEN 1 ELSE 0 END) AS done
             FROM actionpdi WHERE pdi_id = ?",
            [$pdiId]
        );
        if (!$row) return;
        $pct    = $row['total'] > 0 ? (int)round($row['done'] * 100 / $row['total']) : 0;
        $statut = $pct >= 100 ? 'Completed' : 'Active';
        $conn->executeStatement(
            "UPDATE pdi SET progressionGlobale = ?, statut = ? WHERE id = ?",
            [$pct, $statut, $pdiId]
        );
    }

    private function syncFormationActions(\Doctrine\DBAL\Connection $conn, int $userId): void
    {
        try {
            // ✅ FIX: camelCase column names
            $actions = $conn->fetchAllAssociative(
                "SELECT a.id, a.typeAction, a.pdi_id, p.employe_id
                 FROM actionpdi a
                 JOIN pdi p ON a.pdi_id = p.id
                 WHERE a.typeAction LIKE 'Formation : %' AND a.statut != 'Completed'"
            );
            foreach ($actions as $a) {
                $titre = substr($a['typeAction'], strlen('Formation : '));
                $fId   = $conn->fetchOne("SELECT id FROM formation WHERE titre = ? OR titre LIKE ? LIMIT 1", [$titre, $titre . '%']);
                if (!$fId) continue;
                $total = (int)($conn->fetchOne("SELECT COUNT(*) FROM module WHERE formation_id = ?", [$fId]) ?: 0);
                if ($total === 0) continue;
                $done = (int)($conn->fetchOne(
                    "SELECT COUNT(*) FROM module_progression mp JOIN module m ON mp.module_id = m.id
                     WHERE m.formation_id = ? AND mp.employe_id = ? AND mp.statut = 'completed'",
                    [$fId, $a['employe_id']]
                ) ?: 0);
                if ($done >= $total) {
                    $conn->executeStatement("UPDATE actionpdi SET statut = 'Completed' WHERE id = ?", [$a['id']]);
                    $this->recalcProgression($conn, $a['pdi_id']);
                }
            }
        } catch (\Exception $e) {}
    }
}