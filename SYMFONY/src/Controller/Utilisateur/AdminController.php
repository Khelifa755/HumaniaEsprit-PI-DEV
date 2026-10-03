<?php

namespace App\Controller\Utilisateur;

use App\Entity\Utilisateur;
use App\Enum\Role;
use App\Service\Utilisateur\CandidatureService;
use App\Service\Utilisateur\MailService;
use App\Service\Utilisateur\UtilisateurService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Replaces:
 *  - AdminDashboardController  → dashboard with stats
 *  - GestionUtilisateurController → create / edit / delete / toggle status
 *  - ArchiveUtilisateurController → archive / restore
 *  - CandidatsAcceptesController  → convert accepted candidatures to employees
 */
#[Route('/admin')]
class AdminController extends AbstractController
{
    public function __construct(
        private readonly UtilisateurService    $utilisateurService,
        private readonly EntityManagerInterface $em,
        private readonly CandidatureService    $candidatureService,
        private readonly MailService           $mailService,
    ) {}

    // ── Dashboard ─────────────────────────────────────────────────────────────

    #[Route('', name: 'app_admin_dashboard')]
    public function dashboard(): Response
    {
        $stats = $this->utilisateurService->getStats();

        // Prepare monthly candidature data for chart
        $conn = $this->em->getConnection();
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

        return $this->render('Utilisateur/dashboard.html.twig', [
            'stats' => $stats,
            'chartMonths' => json_encode($months),
            'chartCounts' => json_encode($counts),
        ]);
    }

    // ── User list ─────────────────────────────────────────────────────────────

    #[Route('/utilisateurs', name: 'app_admin_users')]
    public function users(): Response
    {
        $utilisateurs = array_filter(
            $this->utilisateurService->recupererActifs(),
            fn($u) => $u->getRole() !== Role::ADMIN
        );

        return $this->render('Utilisateur/index.html.twig', [
            'utilisateurs' => array_values($utilisateurs),
            'roles'        => $this->getAssignableRoles(),
        ]);
    }

    // ── Edit user ─────────────────────────────────────────────────────────────

    #[Route('/utilisateurs/{id}/modifier', name: 'app_admin_user_edit', methods: ['GET', 'POST'])]
    public function editUser(int $id, Request $request): Response
    {
        $repo = $this->em->getRepository(Utilisateur::class);
        $u = $repo->find($id);
        if (!$u) throw $this->createNotFoundException("Utilisateur #$id introuvable.");

        $error    = null;
        $managers = $this->utilisateurService->getManagers();

        if ($request->isMethod('POST')) {
            try {
                $newEmail    = trim($request->request->get('email', ''));
                $newUsername = trim($request->request->get('username', ''));
                $newPassword = $request->request->get('mot_de_passe', '');

                if ($this->utilisateurService->emailExistePourAutre($id, $newEmail)) {
                    throw new \RuntimeException("Cet email est déjà utilisé par un autre compte.");
                }

                $this->populateUserFromRequest($u, $request);
                $this->utilisateurService->modifierAvecMotDePasse($u, $newPassword ?: null);
                $this->addFlash('success', 'Utilisateur mis à jour.');
                return $this->redirectToRoute('app_admin_users');
            } catch (\Exception $e) {
                $error = $e->getMessage();
            }
        }

        return $this->render('Utilisateur/edit.html.twig', [
            'utilisateur' => $u,
            'roles'       => $this->getAssignableRoles(),
            'managers'    => $managers,
            'error'       => $error,
        ]);
    }

    // ── Archive user ──────────────────────────────────────────────────────────

    #[Route('/utilisateurs/{id}/archiver', name: 'app_admin_user_archive', methods: ['POST'])]
    public function archiveUser(int $id): Response
    {
        $repo = $this->em->getRepository(Utilisateur::class);
        $u = $repo->find($id);
        if ($u) {
            $this->utilisateurService->archiverUtilisateur($u);
            $this->addFlash('success', 'Utilisateur archivé.');
        }
        return $this->redirectToRoute('app_admin_archives');
    }

    // ── Toggle status (bloquer / activer) ─────────────────────────────────────

    #[Route('/utilisateurs/{id}/statut', name: 'app_admin_user_toggle_status', methods: ['POST'])]
    public function toggleStatus(int $id, Request $request): Response
    {
        $repo = $this->em->getRepository(Utilisateur::class);
        $u = $repo->find($id);
        if ($u) {
            $newStatut = $request->request->get('statut', 'Actif');
            $u->setStatut($newStatut);
            $this->utilisateurService->modifier($u);
            $this->addFlash('success', "Statut mis à jour : $newStatut.");
        }
        return $this->redirectToRoute('app_admin_users');
    }

    // ── Archive ───────────────────────────────────────────────────────────────

    #[Route('/archives', name: 'app_admin_archives')]
    public function archives(): Response
    {
        return $this->render('Utilisateur/archives.html.twig', [
            'utilisateurs' => $this->utilisateurService->recupererArchives(),
        ]);
    }

    #[Route('/utilisateurs/{id}/restaurer', name: 'app_admin_user_restore', methods: ['POST'])]
    public function restoreUser(int $id): Response
    {
        $repo = $this->em->getRepository(Utilisateur::class);
        $u = $repo->find($id);
        if ($u) {
            $this->utilisateurService->restaurerUtilisateur($u);
            $this->addFlash('success', 'Utilisateur restauré.');
        }
        return $this->redirectToRoute('app_admin_archives');
    }

    // ── Accepted candidatures → employee conversion ────────────────────────────

    #[Route('/candidats-acceptes', name: 'app_admin_candidats_acceptes')]
    public function candidatsAcceptes(): Response
    {
        return $this->render('Utilisateur/candidats_acceptes.html.twig', [
            'candidatures' => $this->candidatureService->getAcceptedCandidatures(),
            'managers'     => $this->utilisateurService->getManagers(),
            'roles'        => $this->getAssignableRoles(),
        ]);
    }

    #[Route('/candidats-acceptes/{id}/convertir', name: 'app_admin_convertir_candidat', methods: ['POST'])]
    public function convertirCandidat(int $id, Request $request): Response
    {
        try {
            if ($this->candidatureService->isDejaConverti($id)) {
                throw new \RuntimeException("Ce candidat a déjà été converti en employé.");
            }

            $u = new Utilisateur();
            $u->setNom(trim($request->request->get('nom', '')));
            $u->setPrenom(trim($request->request->get('prenom', '')));
            $u->setRole(Role::from($request->request->get('role', Role::EMPLOYE->value)));
            $u->setStatut('Actif');

            $candidatEmail = trim($request->request->get('email', ''));
            $managerId     = $request->request->get('manager_id') ?: null;
            $matricule     = trim($request->request->get('matricule', ''));
            $posteActuel   = trim($request->request->get('poste_actuel', ''));
            $departement   = trim($request->request->get('departement', ''));

            $result = $this->utilisateurService->convertirCandidatEnUtilisateur(
                $u,
                $id,
                $candidatEmail,
                $managerId ? (int)$managerId : null,
                null,
                $matricule,
                $posteActuel,
                $departement
            );

            // Send credentials by email
            $this->mailService->envoyerCredentials(
                $candidatEmail,
                $candidatEmail,
                $result['rawPassword']
            );

            $this->addFlash(
                'success',
                "Compte créé pour {$u->getFullName()}. Identifiants envoyés à $candidatEmail."
            );
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('app_admin_candidats_acceptes');
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /**
     * Get roles that can be assigned to employees.
     * Excludes ADMIN and CANDIDAT roles - only staff roles.
     * Manager can be assigned to many employees (one-to-many relationship via manager_id)
     */
    private function getAssignableRoles(): array
    {
        return [
            Role::RH,
            Role::MANAGER,
            Role::FORMATEUR,
            Role::EMPLOYE,
        ];
    }

    private function populateUserFromRequest(Utilisateur $u, Request $request): void
    {
        $u->setNom(trim($request->request->get('nom', '')));
        $u->setPrenom(trim($request->request->get('prenom', '')));
        $u->setEmail(trim($request->request->get('email', '')));
        $u->setUsername(trim($request->request->get('username', '')));
        $u->setNumtel(trim($request->request->get('numtel', '')));
        $u->setStatut($request->request->get('statut', 'Actif'));

        $roleStr = $request->request->get('role', Role::EMPLOYE->value);
        try {
            $u->setRole(Role::from($roleStr));
        } catch (\ValueError) {
            $u->setRole(Role::EMPLOYE);
        }

        $u->setPosteActuel(trim($request->request->get('poste_actuel', '')) ?: null);
        $u->setMatricule(trim($request->request->get('matricule', '')) ?: null);
        $u->setDepartement(trim($request->request->get('departement', '')) ?: null);

        $managerId = $request->request->get('manager_id');
        $u->setManagerId($managerId ? (int)$managerId : null);

        $dateEmbauche = $request->request->get('date_embauche', '');
        $u->setDateEmbauche($dateEmbauche ? new \DateTime($dateEmbauche) : null);
    }

    // ── Export PDF ─────────────────────────────────────────────────────────────

    #[Route('/export/pdf', name: 'app_admin_export_pdf', methods: ['GET'])]
    public function exportPdf(): Response
    {
        $utilisateurs = $this->utilisateurService->recupererActifs();

        // Create HTML content for PDF (print-friendly)
        $html = $this->renderView('Utilisateur/export_pdf.html.twig', [
            'utilisateurs' => $utilisateurs,
            'generatedAt' => new \DateTime(),
        ]);

        return new Response($html, 200, [
            'Content-Type' => 'text/html; charset=utf-8',
            'Content-Disposition' => 'inline; filename="utilisateurs.html"',
        ]);
    }

    // ── Export Excel (CSV format - opens in Excel) ────────────────────────────

    #[Route('/export/excel', name: 'app_admin_export_excel', methods: ['GET'])]
    public function exportExcel(): Response
    {
        $utilisateurs = $this->utilisateurService->recupererActifs();

        $response = new StreamedResponse(function () use ($utilisateurs) {
            $handle = fopen('php://output', 'w');

            // Set UTF-8 BOM for Excel to recognize encoding
            fwrite($handle, "\xEF\xBB\xBF");

            // Headers
            $headers = ['ID', 'Nom', 'Prénom', 'Email', 'Téléphone', 'Rôle', 'Statut', 'Poste', 'Département'];
            fputcsv($handle, $headers, ';');

            // Data rows
            foreach ($utilisateurs as $u) {
                $row = [
                    $u->getId(),
                    $u->getNom(),
                    $u->getPrenom(),
                    $u->getEmail(),
                    $u->getNumtel() ?: '—',
                    $u->getRoleValue(),
                    $u->getStatut(),
                    $u->getPosteActuel() ?: '—',
                    $u->getDepartement() ?: '—'
                ];
                fputcsv($handle, $row, ';');
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="utilisateurs_' . date('Y-m-d_His') . '.csv"');

        return $response;
    }
}