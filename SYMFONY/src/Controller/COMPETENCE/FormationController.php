<?php

namespace App\Controller\COMPETENCE;

use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/formation')]
class FormationController extends AbstractController
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
    //  CATALOG
    // ══════════════════════════════════════════════════════
    #[Route('/catalog', name: 'formation_catalog', methods: ['GET'])]
    public function catalog(Request $request, EntityManagerInterface $em, PaginatorInterface $paginator): Response
    {
        $conn = $em->getConnection();

        $search    = $request->query->get('search', '');
        $fType     = $request->query->get('type', '');
        $fCategory = $request->query->get('categorie', '');
        $fStatut   = $request->query->get('statut', '');

        $currentUserId = $this->getCurrentUserId();

        // Stats
        $total     = (int)($conn->fetchOne("SELECT COUNT(*) FROM formation") ?: 0);
        $eLearning = (int)($conn->fetchOne("SELECT COUNT(*) FROM formation WHERE typeFormation='E_LEARNING'") ?: 0);
        $classroom = (int)($conn->fetchOne("SELECT COUNT(*) FROM formation WHERE typeFormation='CLASSROOM'") ?: 0);
        $totalCats = (int)($conn->fetchOne("SELECT COUNT(*) FROM categorieformation") ?: 0);

        // Filtres dynamiques
        $where  = [];
        $params = [];
        if ($search) {
            $where[] = 'f.titre LIKE ?';
            $params[] = "%$search%";
        }
        if ($fType) {
            $where[] = 'f.typeFormation = ?';
            $params[] = $fType;
        }
        if ($fCategory) {
            $where[] = 'cf.id = ?';
            $params[] = $fCategory;
        }
        if ($fStatut) {
            $where[] = 'f.statutFormation = ?';
            $params[] = $fStatut;
        }

        $whereStr = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        // ── MES FORMATIONS (inscriptions de l'utilisateur connecté) ──
        $mesWhereArr = array_merge($where, ['i.employe_id = ?']);
        $mesParams   = array_merge($params, [$currentUserId]);
        $mesWhere    = 'WHERE ' . implode(' AND ', $mesWhereArr);

        $mesFormations = $conn->fetchAllAssociative("
    SELECT
        f.id, f.titre, f.duree, f.cout, f.typeFormation,
        f.description, f.statutFormation,
        cf.libelle AS categorie_libelle,
        cf.couleur AS categorie_couleur,
        CONCAT(u.prenom, ' ', u.nom) AS formateur_nom,
        i.statut        AS insc_statut,
        i.progression   AS insc_progression,
        s.lieu
    FROM inscriptionformation i
    JOIN sessionformation s  ON i.session_id   = s.id
    JOIN formation f         ON s.formation_id = f.id
    LEFT JOIN categorieformation cf ON f.categorie_id  = cf.id
    LEFT JOIN utilisateur u         ON f.formateur_id  = u.id
    $mesWhere
    ORDER BY f.titre
    LIMIT 99
", $mesParams);

        // Calcul progression en PHP après la requête
        foreach ($mesFormations as &$row) {
            $fid   = $row['id'];
            $total = (int)($conn->fetchOne("SELECT COUNT(*) FROM module WHERE formation_id = ?", [$fid]) ?: 0);
            $done  = $total > 0 ? (int)($conn->fetchOne(
                "SELECT COUNT(*) FROM module_progression mp
         JOIN module m ON mp.module_id = m.id
         WHERE m.formation_id = ? AND mp.employe_id = ? AND mp.statut = 'completed'",
                [$fid, $currentUserId]
            ) ?: 0) : 0;
            $row['total_modules']      = $total;
            $row['done_modules']       = $done;
            $row['progression_dynamic'] = $total > 0 ? (int) round($done * 100 / $total) : (int)$row['insc_progression'];
        }
        unset($row);

        // IDs déjà inscrits pour les exclure de "Autres formations"
        $inscritIds = array_column($mesFormations, 'id');

        // ── AUTRES FORMATIONS (non inscrit) ──
        $autresWhereArr = $where;
        $autresParams   = $params;
        if (!empty($inscritIds)) {
            $placeholders     = implode(',', array_fill(0, count($inscritIds), '?'));
            $autresWhereArr[] = "f.id NOT IN ($placeholders)";
            $autresParams     = array_merge($autresParams, $inscritIds);
        }
        $autresWhere = !empty($autresWhereArr) ? 'WHERE ' . implode(' AND ', $autresWhereArr) : '';

        $autresFormations = $conn->fetchAllAssociative("
            SELECT
                f.id, f.titre, f.duree, f.cout, f.typeFormation,
                f.description, f.statutFormation,
                cf.libelle AS categorie_libelle,
                cf.couleur AS categorie_couleur,
                CONCAT(u.prenom, ' ', u.nom) AS formateur_nom,
                (SELECT COUNT(*) FROM module WHERE formation_id = f.id) AS total_modules,
                (SELECT s2.dateDebut FROM sessionformation s2
                 WHERE s2.formation_id = f.id AND s2.dateFin >= CURDATE()
                 ORDER BY s2.dateDebut ASC LIMIT 1) AS next_session_debut,
                (SELECT s2.statut FROM sessionformation s2
                 WHERE s2.formation_id = f.id AND s2.dateFin >= CURDATE()
                 ORDER BY s2.dateDebut ASC LIMIT 1) AS next_session_statut
            FROM formation f
            LEFT JOIN categorieformation cf ON f.categorie_id = cf.id
            LEFT JOIN utilisateur u         ON f.formateur_id = u.id
            $autresWhere
            ORDER BY f.titre
            LIMIT 99
        ", $autresParams);

        $categories = $conn->fetchAllAssociative("SELECT id, libelle FROM categorieformation ORDER BY libelle");

        // ── Pagination via KnpPaginator ───────────────────────────────────
        $autresPagination = $paginator->paginate(
            $autresFormations,
            $request->query->getInt('page', 1),
            6  // 6 formations par page
        );

        $mesPagination = $paginator->paginate(
            $mesFormations,
            $request->query->getInt('mes_page', 1),
            6
        );

        return $this->render('competence/formations/formation_catalog.html.twig', [
            'mesPagination'    => $mesPagination,
            'autresPagination' => $autresPagination,
            'categories'       => $categories,
            'search'           => $search,
            'fType'            => $fType,
            'fCategory'        => $fCategory,
            'fStatut'          => $fStatut,
            'types'            => $this->getTypeLabels(),
            'stats'            => [
                'total'     => $total,
                'eLearning' => $eLearning,
                'classroom' => $classroom,
                'totalCats' => $totalCats,
            ],
        ]);
    }

    // ══════════════════════════════════════════════════════
    //  NEW
    // ══════════════════════════════════════════════════════
    #[Route('/new', name: 'formation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyUnlessAdminRole();
        $conn = $em->getConnection();

        if ($request->isMethod('POST')) {
            $errors = $this->validateFormation($request);
            if (!empty($errors)) {
                foreach ($errors as $e) $this->addFlash('error', $e);
            } else {
                $conn->executeStatement(
                    "INSERT INTO formation
                        (titre, description, duree, cout, typeFormation, statutFormation, categorie_id, formateur_id)
                     VALUES (?, ?, ?, ?, ?, 'Active', ?, ?)",
                    [
                        trim($request->request->get('titre')),
                        $request->request->get('description', ''),
                        (int)$request->request->get('duree'),
                        (float)$request->request->get('cout', 0),
                        $request->request->get('typeFormation'),
                        $request->request->get('categorie_id') ?: null,
                        $request->request->get('formateur_id') ?: null,
                    ]
                );
                $newId  = (int)$conn->lastInsertId();
                $sDebut = $request->request->get('session_debut');
                $sFin   = $request->request->get('session_fin');
                $sLieu  = $request->request->get('session_lieu', 'En ligne');

                if ($sDebut && $sFin) {
                    if ($sDebut >= $sFin) {
                        $this->addFlash('error', '⚠️ La date de début doit être avant la date de fin.');
                    } else {
                        $conn->executeStatement(
                            "INSERT INTO sessionformation (dateDebut, dateFin, lieu, statut, formation_id)
                             VALUES (?, ?, ?, 'Planifiée', ?)",
                            [$sDebut, $sFin, $sLieu ?: 'En ligne', $newId]
                        );
                    }
                }

                $this->addFlash('success', '✅ Formation créée avec succès !');
                return $this->redirectToRoute('formation_catalog');
            }
        }

        $categories = $conn->fetchAllAssociative("SELECT id, libelle FROM categorieformation ORDER BY libelle");
        $formateurs = $conn->fetchAllAssociative("SELECT id, CONCAT(prenom,' ',nom) AS nom FROM utilisateur ORDER BY nom");

        return $this->render('competence/formations/formation_new.html.twig', [
            'categories' => $categories,
            'formateurs' => $formateurs,
            'types'      => $this->getTypeLabels(),
            'formData'   => $request->request->all(),
        ]);
    }

    // ══════════════════════════════════════════════════════
    //  EDIT
    // ══════════════════════════════════════════════════════
    #[Route('/{id}/edit', name: 'formation_edit', methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyUnlessManagerRole();
        $conn = $em->getConnection();

        $formation = $conn->fetchAssociative(
            "SELECT f.*, cf.libelle AS cat_libelle
             FROM formation f
             LEFT JOIN categorieformation cf ON f.categorie_id = cf.id
             WHERE f.id = ?",
            [$id]
        );
        if (!$formation) throw $this->createNotFoundException("Formation #$id introuvable.");

        if ($request->isMethod('POST')) {
            $errors = $this->validateFormation($request);
            if (!empty($errors)) {
                foreach ($errors as $e) $this->addFlash('error', $e);
            } else {
                $conn->executeStatement(
                    "UPDATE formation
                     SET titre=?, description=?, duree=?, cout=?,
                         typeFormation=?, categorie_id=?, formateur_id=?
                     WHERE id=?",
                    [
                        trim($request->request->get('titre')),
                        $request->request->get('description', ''),
                        (int)$request->request->get('duree'),
                        (float)$request->request->get('cout', 0),
                        $request->request->get('typeFormation'),
                        $request->request->get('categorie_id') ?: null,
                        $request->request->get('formateur_id') ?: null,
                        $id,
                    ]
                );
                $this->addFlash('success', '✅ Formation mise à jour !');
                return $this->redirectToRoute('formation_catalog');
            }
        }

        $categories = $conn->fetchAllAssociative("SELECT id, libelle FROM categorieformation ORDER BY libelle");
        $formateurs = $conn->fetchAllAssociative("SELECT id, CONCAT(prenom,' ',nom) AS nom FROM utilisateur ORDER BY nom");
        $sessions   = $conn->fetchAllAssociative(
            "SELECT s.*, (SELECT COUNT(*) FROM inscriptionformation WHERE session_id=s.id) AS nb_inscrits
             FROM sessionformation s WHERE formation_id=? ORDER BY dateDebut DESC",
            [$id]
        );

        return $this->render('competence/formations/formation_edit.html.twig', [
            'formation'  => $formation,
            'categories' => $categories,
            'formateurs' => $formateurs,
            'sessions'   => $sessions,
            'types'      => $this->getTypeLabels(),
        ]);
    }

    // ══════════════════════════════════════════════════════
    //  TOGGLE STATUT
    // ══════════════════════════════════════════════════════
    #[Route('/{id}/toggle', name: 'formation_toggle', methods: ['POST'])]
    public function toggle(int $id, EntityManagerInterface $em): Response
    {
        $this->denyUnlessManagerRole();
        $conn    = $em->getConnection();
        $current = $conn->fetchOne("SELECT statutFormation FROM formation WHERE id=?", [$id]);
        if (!$current) throw $this->createNotFoundException("Formation #$id introuvable.");

        $conn->executeStatement(
            "UPDATE formation SET statutFormation=? WHERE id=?",
            [$current === 'Active' ? 'Inactive' : 'Active', $id]
        );
        $this->addFlash('success', '✅ Statut mis à jour.');
        return $this->redirectToRoute('formation_catalog');
    }

    // ══════════════════════════════════════════════════════
    //  S'INSCRIRE
    // ══════════════════════════════════════════════════════
    #[Route('/{id}/inscrire', name: 'formation_inscrire', methods: ['POST'])]
    public function inscrire(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $conn   = $em->getConnection();
        $userId = $this->getCurrentUserId();

        // Règle métier : pas de double inscription
        $existe = $conn->fetchOne(
            "SELECT i.id FROM inscriptionformation i
             JOIN sessionformation s ON i.session_id = s.id
             WHERE s.formation_id=? AND i.employe_id=? LIMIT 1",
            [$id, $userId]
        );
        if ($existe) {
            $this->addFlash('error', '⚠️ Cet employé est déjà inscrit à cette formation.');
            return $this->redirectToRoute('formation_catalog');
        }

        // Meilleure session disponible
        $session = $conn->fetchAssociative(
            "SELECT id FROM sessionformation
             WHERE formation_id=? AND dateFin >= CURDATE()
             ORDER BY CASE statut
                 WHEN 'Planifiée' THEN 1 WHEN 'Open' THEN 2 WHEN 'Active' THEN 3 ELSE 4
             END ASC, dateDebut ASC LIMIT 1",
            [$id]
        ) ?: $conn->fetchAssociative(
            "SELECT id FROM sessionformation WHERE formation_id=? ORDER BY dateDebut DESC LIMIT 1",
            [$id]
        );

        if (!$session) {
            $this->addFlash('error', '⚠️ Aucune session disponible.');
            return $this->redirectToRoute('formation_catalog');
        }

        $conn->executeStatement(
            "INSERT INTO inscriptionformation (dateInscription, statut, progression, noteFinale, session_id, employe_id)
             VALUES (CURDATE(), 'In Progress', 0, 0, ?, ?)",
            [$session['id'], $userId]
        );

        $titre = $conn->fetchOne("SELECT titre FROM formation WHERE id=?", [$id]);
        $this->addFlash('success', '🎉 Inscription à « ' . $titre . ' » confirmée !');
        return $this->redirectToRoute('formation_catalog');
    }

    // ══════════════════════════════════════════════════════
    //  AJOUTER SESSION
    // ══════════════════════════════════════════════════════
    #[Route('/{id}/session/add', name: 'formation_session_add', methods: ['POST'])]
    public function addSession(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyUnlessManagerRole();
        $conn  = $em->getConnection();
        $debut = $request->request->get('dateDebut');
        $fin   = $request->request->get('dateFin');
        $lieu  = trim($request->request->get('lieu', 'En ligne'));

        if (!$debut || !$fin) {
            $this->addFlash('error', '⚠️ Les dates sont obligatoires.');
        } elseif ($debut >= $fin) {
            $this->addFlash('error', '⚠️ La date de début doit être avant la date de fin.');
        } else {
            $conn->executeStatement(
                "INSERT INTO sessionformation (dateDebut, dateFin, lieu, statut, formation_id)
                 VALUES (?, ?, ?, 'Planifiée', ?)",
                [$debut, $fin, $lieu ?: 'En ligne', $id]
            );
            $this->addFlash('success', '✅ Session ajoutée.');
        }

        return $this->redirectToRoute('formation_edit', ['id' => $id]);
    }

    // ══════════════════════════════════════════════════════
    //  SUPPRIMER SESSION
    // ══════════════════════════════════════════════════════
    #[Route('/session/{sid}/delete', name: 'formation_session_delete', methods: ['POST'])]
    public function deleteSession(int $sid, EntityManagerInterface $em): Response
    {
        $this->denyUnlessManagerRole();
        $conn    = $em->getConnection();
        $session = $conn->fetchAssociative("SELECT * FROM sessionformation WHERE id=?", [$sid]);
        if (!$session) throw $this->createNotFoundException("Session #$sid introuvable.");

        $nbInscrits = (int)($conn->fetchOne("SELECT COUNT(*) FROM inscriptionformation WHERE session_id=?", [$sid]) ?: 0);
        if ($nbInscrits > 0) {
            $this->addFlash('error', "⚠️ Impossible : $nbInscrits inscription(s) sur cette session.");
        } else {
            $conn->executeStatement("DELETE FROM sessionformation WHERE id=?", [$sid]);
            $this->addFlash('success', '✅ Session supprimée.');
        }

        return $this->redirectToRoute('formation_edit', ['id' => $session['formation_id']]);
    }

    // ══════════════════════════════════════════════════════
    //  DELETE FORMATION
    // ══════════════════════════════════════════════════════
    #[Route('/{id}/delete', name: 'formation_delete', methods: ['POST'])]
    public function delete(int $id, EntityManagerInterface $em): Response
    {
        $this->denyUnlessManagerRole();
        $conn = $em->getConnection();
        $nb   = (int)($conn->fetchOne(
            "SELECT COUNT(*) FROM inscriptionformation i
             JOIN sessionformation s ON i.session_id=s.id WHERE s.formation_id=?",
            [$id]
        ) ?: 0);

        if ($nb > 0) {
            $this->addFlash('error', "⚠️ Impossible : $nb inscription(s) existante(s).");
        } else {
            $conn->executeStatement("DELETE FROM sessionformation WHERE formation_id=?", [$id]);
            $conn->executeStatement("DELETE FROM formation WHERE id=?", [$id]);
            $this->addFlash('success', '✅ Formation supprimée.');
        }

        return $this->redirectToRoute('formation_catalog');
    }

    // ══════════════════════════════════════════════════════
    //  SE DÉSINSCRIRE
    // ══════════════════════════════════════════════════════
    #[Route('/{id}/desinscrire', name: 'formation_desinscrire', methods: ['POST'])]
    public function desinscrire(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $conn   = $em->getConnection();
        $userId = $this->getCurrentUserId();

        // Trouver l'inscription
        $inscription = $conn->fetchAssociative(
            "SELECT i.id FROM inscriptionformation i
             JOIN sessionformation s ON i.session_id = s.id
             WHERE s.formation_id = ? AND i.employe_id = ? LIMIT 1",
            [$id, $userId]
        );

        if (!$inscription) {
            $this->addFlash('error', '⚠️ Inscription introuvable.');
            return $this->redirectToRoute('formation_catalog');
        }

        // Supprimer la progression des modules
        $conn->executeStatement(
            "DELETE mp FROM module_progression mp
             JOIN module m ON mp.module_id = m.id
             WHERE m.formation_id = ? AND mp.employe_id = ?",
            [$id, $userId]
        );

        // Supprimer l'inscription
        $conn->executeStatement(
            "DELETE FROM inscriptionformation WHERE id = ?",
            [$inscription['id']]
        );

        $titre = $conn->fetchOne("SELECT titre FROM formation WHERE id = ?", [$id]);
        $this->addFlash('success', '✅ Désinscription de « ' . $titre . ' » effectuée.');
        return $this->redirectToRoute('formation_catalog');
    }

    // ══════════════════════════════════════════════════════
    //  DETAILS AJAX — modules d'une formation (popup)
    // ══════════════════════════════════════════════════════
    #[Route('/{id}/details', name: 'formation_details', methods: ['GET'])]
    public function details(int $id, EntityManagerInterface $em): JsonResponse
    {
        $conn = $em->getConnection();

        $formation = $conn->fetchAssociative(
            "SELECT f.id, f.titre, f.description, f.duree, f.cout, f.typeFormation,
                    f.statutFormation,
                    COALESCE(cf.libelle, 'Autre') AS categorie,
                    COALESCE(cf.couleur, '#6366F1') AS categorie_couleur,
                    COALESCE(CONCAT(u.prenom,' ',u.nom), '') AS formateur_nom
             FROM formation f
             LEFT JOIN categorieformation cf ON f.categorie_id = cf.id
             LEFT JOIN utilisateur u ON f.formateur_id = u.id
             WHERE f.id = ?",
            [$id]
        );

        if (!$formation) {
            return new JsonResponse(['ok' => false, 'error' => 'Formation introuvable'], 404);
        }

        $modules = $conn->fetchAllAssociative(
            "SELECT id, titre, type_contenu, duree_minutes, ordre, description
             FROM module WHERE formation_id = ? ORDER BY ordre ASC",
            [$id]
        );

        // Estimation temps total en heures/demi-journée
        $totalMinutes = array_sum(array_column($modules, 'duree_minutes'));
        $estimation = '';
        if ($totalMinutes > 0) {
            if ($totalMinutes < 240)       $estimation = 'Demi-journée';
            elseif ($totalMinutes <= 480)  $estimation = '1 journée';
            elseif ($totalMinutes <= 960)  $estimation = '2 journées';
            else                           $estimation = round($totalMinutes / 480) . ' journées';
        }

        return new JsonResponse([
            'ok'         => true,
            'formation'  => $formation,
            'modules'    => $modules,
            'estimation' => $estimation,
        ]);
    }

    // ══════════════════════════════════════════════════════
    //  HELPERS
    // ══════════════════════════════════════════════════════
    private function validateFormation(Request $r): array
    {
        $errors = [];
        $titre  = trim($r->request->get('titre', ''));

        if (strlen($titre) < 3)   $errors[] = 'Le titre doit contenir au moins 3 caractères.';
        if (strlen($titre) > 255) $errors[] = 'Le titre ne peut pas dépasser 255 caractères.';

        $duree = (int)$r->request->get('duree', 0);
        if ($duree < 1 || $duree > 500) $errors[] = 'La durée doit être entre 1 et 500 heures.';

        $cout = $r->request->get('cout', '0');
        if (!is_numeric($cout) || (float)$cout < 0) $errors[] = 'Le coût doit être un nombre positif.';

        if (!in_array($r->request->get('typeFormation'), $this->getTypes()))
            $errors[] = 'Type de formation invalide.';

        return $errors;
    }

    private function getTypes(): array
    {
        return ['CLASSROOM', 'E_LEARNING', 'BLENDED', 'COACHING', 'MENTORING'];
    }

    private function getTypeLabels(): array
    {
        return [
            'CLASSROOM'  => ['label' => 'Classroom',  'icon' => '🏫', 'color' => '#F59E0B'],
            'E_LEARNING' => ['label' => 'E-Learning', 'icon' => '💻', 'color' => '#3B82F6'],
            'BLENDED'    => ['label' => 'Blended',    'icon' => '🔀', 'color' => '#8B5CF6'],
            'COACHING'   => ['label' => 'Coaching',   'icon' => '🎯', 'color' => '#10B981'],
            'MENTORING'  => ['label' => 'Mentoring',  'icon' => '🤝', 'color' => '#EC4899'],
        ];
    }

    #[Route('/ai-assistant', name: 'formation_ai_assistant', methods: ['POST'])]
    public function aiAssistant(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $conn          = $em->getConnection();
        $currentUserId = $this->getCurrentUserId();

        $userMessage = trim($request->request->get('message', ''));
        $history     = json_decode($request->request->get('history', '[]'), true) ?: [];

        if (empty($userMessage)) {
            return new JsonResponse(['ok' => false, 'error' => 'Message vide'], 400);
        }

        // ── 1. Build employee context from DB ─────────────────────────────
        $ctx = $this->buildAiContext($conn, $currentUserId);

        // ── 2. Check if question is in scope ──────────────────────────────
        //    (The system prompt handles this, but we pass a strict instruction)

        // ── 3. Call Groq API ──────────────────────────────────────────────
        $groqKey = $this->getParameter('groq_api_key');

        try {
            $answer = $this->callGroqAssistant($groqKey, $userMessage, $history, $ctx);
            return new JsonResponse(['ok' => true, 'response' => $answer]);
        } catch (\Exception $e) {
            return new JsonResponse(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // ── Context Builder ────────────────────────────────────────────────────
    private function buildAiContext(\Doctrine\DBAL\Connection $conn, int $userId): string
    {
        $ctx = '';

        // User info
        try {
            $user = $conn->fetchAssociative(
                "SELECT CONCAT(prenom,' ',nom) AS fullName, role, posteActuel, departement
                 FROM utilisateur WHERE id = ?",
                [$userId]
            );
            if ($user) {
                $ctx .= "Employé : {$user['fullName']}\n";
                $ctx .= "Rôle : {$user['role']}\n";
                if ($user['posteActuel']) $ctx .= "Poste : {$user['posteActuel']}\n";
                if ($user['departement']) $ctx .= "Département : {$user['departement']}\n\n";
            }
        } catch (\Exception $e) {
        }

        // Compétences
        try {
            $skills = $conn->fetchAllAssociative(
                "SELECT c.libelle, ce.niveauActuel, c.niveauMax, ce.niveauValide,
                        COALESCE(cat.libelle,'Général') AS categorie
                 FROM competenceemploye ce
                 JOIN competence c ON ce.competence_id = c.id
                 LEFT JOIN categoriecompetence cat ON c.categorie_id = cat.id
                 WHERE ce.employe_id = ?
                 ORDER BY ce.niveauActuel DESC LIMIT 15",
                [$userId]
            );
            if ($skills) {
                $ctx .= "COMPÉTENCES :\n";
                foreach ($skills as $s) {
                    $ctx .= "- {$s['libelle']} : {$s['niveauActuel']}/{$s['niveauMax']}";
                    if ($s['niveauValide']) $ctx .= " ✓";
                    $ctx .= " [{$s['categorie']}]\n";
                }
                $ctx .= "\n";
            } else {
                $ctx .= "COMPÉTENCES : aucune enregistrée\n\n";
            }
        } catch (\Exception $e) {
            $ctx .= "COMPÉTENCES : non disponibles\n\n";
        }

        // Formations en cours
        try {
            $formations = $conn->fetchAllAssociative(
                "SELECT f.titre, inf.statut, inf.progression,
                        COALESCE(cat.libelle,'Autre') AS categorie
                 FROM inscriptionformation inf
                 JOIN sessionformation sf ON inf.session_id = sf.id
                 JOIN formation f ON sf.formation_id = f.id
                 LEFT JOIN categorieformation cat ON f.categorie_id = cat.id
                 WHERE inf.employe_id = ?
                 ORDER BY inf.dateInscription DESC LIMIT 8",
                [$userId]
            );
            if ($formations) {
                $ctx .= "FORMATIONS :\n";
                foreach ($formations as $f) {
                    $ctx .= "- {$f['titre']} — {$f['statut']} ({$f['progression']}%) [{$f['categorie']}]\n";
                }
                $ctx .= "\n";
            } else {
                $ctx .= "FORMATIONS : aucune inscription\n\n";
            }
        } catch (\Exception $e) {
            $ctx .= "FORMATIONS : non disponibles\n\n";
        }

        // PDI actif
        try {
            $pdi = $conn->fetchAssociative(
                "SELECT p.annee, p.progressionGlobale, p.statut,
                        COUNT(a.id) AS nb_actions,
                        SUM(CASE WHEN a.statut='Completed' THEN 1 ELSE 0 END) AS done_actions
                 FROM pdi p
                 LEFT JOIN actionpdi a ON a.pdi_id = p.id
                 WHERE p.employe_id = ?
                 GROUP BY p.id ORDER BY p.annee DESC LIMIT 1",
                [$userId]
            );
            if ($pdi) {
                $ctx .= "PDI {$pdi['annee']} : progression {$pdi['progressionGlobale']}%, statut {$pdi['statut']}, {$pdi['done_actions']}/{$pdi['nb_actions']} actions\n\n";
            } else {
                $ctx .= "PDI : aucun PDI actif\n\n";
            }
        } catch (\Exception $e) {
            $ctx .= "PDI : non disponible\n\n";
        }

        // Évaluations récentes
        try {
            $evals = $conn->fetchAllAssociative(
                "SELECT ef.titre, re.score_pct, re.statut, re.datePassage
                 FROM resultatevaluation re
                 JOIN evaluationformation ef ON re.evaluation_id = ef.id
                 WHERE re.employe_id = ?
                 ORDER BY re.datePassage DESC LIMIT 5",
                [$userId]
            );
            if ($evals) {
                $ctx .= "ÉVALUATIONS RÉCENTES :\n";
                foreach ($evals as $ev) {
                    $ctx .= "- {$ev['titre']} : {$ev['score_pct']}% ({$ev['statut']})\n";
                }
                $ctx .= "\n";
            }
        } catch (\Exception $e) {
        }

        // Gaps détectés (compétences < 3)
        try {
            $gaps = $conn->fetchAllAssociative(
                "SELECT c.libelle, ce.niveauActuel, c.niveauMax
                 FROM competenceemploye ce
                 JOIN competence c ON ce.competence_id = c.id
                 WHERE ce.employe_id = ? AND ce.niveauActuel < 3 AND c.statutCompetence = 'ACTIF'
                 LIMIT 8",
                [$userId]
            );
            if ($gaps) {
                $ctx .= "GAPS DÉTECTÉS (niveau < 3) :\n";
                foreach ($gaps as $g) {
                    $ctx .= "- {$g['libelle']} : {$g['niveauActuel']}/{$g['niveauMax']}\n";
                }
            }
        } catch (\Exception $e) {
        }

        return $ctx;
    }

    // ── Groq API Call ──────────────────────────────────────────────────────
    private function callGroqAssistant(string $apiKey, string $message, array $history, string $context): string
    {
        if (empty($apiKey)) throw new \RuntimeException("Clé Groq non configurée");

        $systemPrompt = "Tu es l'AI Learning Assistant de Humania, une plateforme RH innovante. " .
            "Tu es un coach IA expert en gestion des compétences, formations professionnelles, évaluations, PDI (Plans de Développement Individuel) et développement de carrière. " .
            "Tu parles en français, avec un ton professionnel mais chaleureux. " .
            "Tu utilises des emojis avec modération. " .
            "Tu structures tes réponses avec des titres (##) et des listes (-) quand c'est pertinent.\n\n" .
            "PROFIL DE L'EMPLOYÉ CONNECTÉ :\n" . $context . "\n\n" .
            "DOMAINES OÙ TU PEUX AIDER :\n" .
            "- Compétences : analyse des gaps, niveaux, catégories\n" .
            "- Formations : recommandations, inscriptions, progression\n" .
            "- Évaluations : résultats, préparation, conseils\n" .
            "- PDI : construction, actions, objectifs\n" .
            "- Parcours de carrière et développement professionnel\n\n" .
            "RÈGLE IMPORTANTE : Si une question ne concerne PAS les compétences, formations, évaluations, PDI ou le développement professionnel dans le contexte RH de Humania, réponds poliment : " .
            "'Je suis spécialisé dans les compétences et formations de Humania. Je ne peux pas répondre à cette question, mais je serais ravi de vous aider sur votre développement professionnel ! Que voulez-vous savoir sur vos compétences ou formations ?' " .
            "Ne discute pas de sujets hors-scope (actualités, code général, recettes, etc.).\n\n" .
            "Utilise le profil de l'employé pour personnaliser tes réponses. Donne des conseils concrets et actionnables.";

        // Build messages array
        $messages = [['role' => 'system', 'content' => $systemPrompt]];

        // Last 8 messages from history
        $historySlice = array_slice($history, -8);
        foreach ($historySlice as $h) {
            if (isset($h['role'], $h['content'])) {
                $messages[] = ['role' => $h['role'], 'content' => $h['content']];
            }
        }
        $messages[] = ['role' => 'user', 'content' => $message];

        $payload = json_encode([
            'model'       => 'llama-3.3-70b-versatile',
            'messages'    => $messages,
            'max_tokens'  => 1024,
            'temperature' => 0.7,
        ]);

        $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
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

        if ($curlErr) throw new \RuntimeException("cURL: $curlErr");
        if ($code !== 200) throw new \RuntimeException("Groq error $code: " . substr($body, 0, 300));

        $data = json_decode($body, true);
        return $data['choices'][0]['message']['content'] ?? 'Réponse non disponible.';
    }
}
