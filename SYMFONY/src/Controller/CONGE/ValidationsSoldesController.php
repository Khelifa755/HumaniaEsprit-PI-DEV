<?php

namespace App\Controller\CONGE;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\DBAL\Connection;
use App\Service\EmailService;

#[Route('/conges')]
class ValidationsSoldesController extends AbstractController
{
    private Connection $db;
    private const QUOTA_ANNUEL_CP = 30;
    private EmailService $emailService;

    public function __construct(Connection $connection, EmailService $emailService)
    {
        $this->db = $connection;
        $this->emailService = $emailService;
    }

    // ══════════════════════════════════════════════════════════
    //  PAGE PRINCIPALE — onglet Validations
    // ══════════════════════════════════════════════════════════

    #[Route('/validations', name: 'conges_validations', methods: ['GET'])]
    public function index(): Response
    {
        $stats      = $this->getStatsValidations();
        $enAttente  = $this->getAbsencesEnAttente();
        $planning   = $this->getPlanning();
        $membres    = $this->getMembres();
        $conges     = $this->getCongesApprouves();

        return $this->render('CONGE/validation/validations_soldes.html.twig', [
            'stats'         => $stats,
            'en_attente'    => $enAttente,
            'planning'      => $planning,
            'membres'       => $membres,
            'conges_calendrier' => $conges,
            'vue'           => 'validations',
        ]);
    }

    // ══════════════════════════════════════════════════════════
    //  PAGE PRINCIPALE — onglet Mes Soldes
    // ══════════════════════════════════════════════════════════

    #[Route('/soldes', name: 'conges_soldes', methods: ['GET'])]
    public function mesSoldes(): Response
    {
        $soldes     = $this->calculerSoldes();
        $feries     = $this->getJoursFeriesTunisie2026();
        $typesConge = $this->getTypesConge();
        $typesAbs   = $this->getTypesAbsence();

        return $this->render('CONGE/validation/validations_soldes.html.twig', [
            'soldes'        => $soldes,
            'feries'        => $feries,
            'types_conge'   => $typesConge,
            'types_absence' => $typesAbs,
            'vue'           => 'soldes',
        ]);
    }

    // ══════════════════════════════════════════════════════════
    //  ACTION : Approuver une absence AVEC notification
    // ══════════════════════════════════════════════════════════

    #[Route('/{id}/approuver', name: 'conges_approuver', methods: ['POST', 'GET'])]
    public function approuver(int $id, Request $request): Response
    {
        // Récupérer l'absence avant mise à jour
        $absence = $this->db->fetchAssociative(
            "SELECT a.*, u.email, u.prenom, u.nom, ta.libelle as type_label 
             FROM absence a
             JOIN utilisateur u ON u.id = a.utilisateur_id
             JOIN type_absence ta ON ta.id = a.type_absence_id
             WHERE a.id = ?",
            [$id]
        );

        if ($absence) {
            // Mettre à jour le statut
            $this->db->executeStatement(
                "UPDATE absence SET statut = 'Approuvé' WHERE id = ?",
                [$id]
            );

            // Envoyer email à l'employé
            try {
                $this->emailService->envoyerNotificationValidation([
                    'employe_email' => $absence['email'],
                    'employe_nom'   => $absence['prenom'] . ' ' . $absence['nom'],
                    'date'          => $absence['date_debut'],
                    'type'          => $absence['type_label'] ?? 'Absence',
                    'statut'        => 'Approuvé',
                ]);
                $this->addFlash('success', 'Demande approuvée avec succès. Un email a été envoyé à l\'employé.');
            } catch (\Exception $e) {
                $this->addFlash('warning', 'Demande approuvée mais l\'email n\'a pas pu être envoyé.');
            }
        } else {
            $this->addFlash('error', 'Demande non trouvée.');
        }

        // Rediriger vers la page des validations
        $referer = $request->headers->get('referer');
        if ($referer && str_contains($referer, 'absence')) {
            return $this->redirectToRoute('app_absence_index');
        }
        
        return $this->redirectToRoute('conges_validations');
    }

    // ══════════════════════════════════════════════════════════
    //  ACTION : Refuser une absence AVEC notification
    // ══════════════════════════════════════════════════════════

    #[Route('/{id}/refuser', name: 'conges_refuser', methods: ['POST', 'GET'])]
    public function refuser(int $id, Request $request): Response
    {
        // Récupérer l'absence avant mise à jour
        $absence = $this->db->fetchAssociative(
            "SELECT a.*, u.email, u.prenom, u.nom, ta.libelle as type_label 
             FROM absence a
             JOIN utilisateur u ON u.id = a.utilisateur_id
             JOIN type_absence ta ON ta.id = a.type_absence_id
             WHERE a.id = ?",
            [$id]
        );

        if ($absence) {
            // Mettre à jour le statut
            $this->db->executeStatement(
                "UPDATE absence SET statut = 'Refusé' WHERE id = ?",
                [$id]
            );

            // Envoyer email à l'employé
            try {
                $this->emailService->envoyerNotificationValidation([
                    'employe_email' => $absence['email'],
                    'employe_nom'   => $absence['prenom'] . ' ' . $absence['nom'],
                    'date'          => $absence['date_debut'],
                    'type'          => $absence['type_label'] ?? 'Absence',
                    'statut'        => 'Refusé',
                ]);
                $this->addFlash('error', 'Demande refusée. Un email a été envoyé à l\'employé.');
            } catch (\Exception $e) {
                $this->addFlash('warning', 'Demande refusée mais l\'email n\'a pas pu être envoyé.');
            }
        } else {
            $this->addFlash('error', 'Demande non trouvée.');
        }

        // Rediriger vers la page des validations
        $referer = $request->headers->get('referer');
        if ($referer && str_contains($referer, 'absence')) {
            return $this->redirectToRoute('app_absence_index');
        }
        
        return $this->redirectToRoute('conges_validations');
    }

    // ══════════════════════════════════════════════════════════
    //  API JSON : données calendrier (pour FullCalendar)
    // ══════════════════════════════════════════════════════════

    #[Route('/api/calendrier', name: 'conges_api_calendrier', methods: ['GET'])]
    public function apiCalendrier(): JsonResponse
    {
        $events = [];
        $colors = ['#667eea', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4', '#ec4899', '#14b8a6', '#f97316', '#84cc16'];

        // ══════════════════════════════════════════════════════════
        //  CONGÉS (tous statuts)
        // ══════════════════════════════════════════════════════════
        $conges = $this->db->fetchAllAssociative(
            "SELECT c.*, u.prenom, u.nom, tc.libelle AS type_label
             FROM conge c
             JOIN utilisateur u ON u.id = c.utilisateur_id
             JOIN type_conge tc ON tc.id = c.type_conge_id
             ORDER BY c.date_debut"
        );

        foreach ($conges as $c) {
            $colorIdx = abs(crc32($c['nom'] . $c['prenom'])) % count($colors);
            
            $statusColor = match(strtolower($c['statut'])) {
                'approuvé' => $colors[$colorIdx],
                'en attente' => '#f59e0b',
                'refusé' => '#94a3b8',
                default => $colors[$colorIdx]
            };

            $events[] = [
                'id'        => 'c_' . $c['id'],
                'title'     => $c['prenom'] . ' ' . $c['nom'] . ' - Congé',
                'start'     => $c['date_debut'],
                'end'       => date('Y-m-d', strtotime($c['date_fin'] . ' +1 day')),
                'color'     => $statusColor,
                'extendedProps' => [
                    'type'   => $c['type_label'],
                    'statut' => $c['statut'],
                ],
            ];
        }

        // ══════════════════════════════════════════════════════════
        //  ABSENCES (tous statuts)
        // ══════════════════════════════════════════════════════════
        $absences = $this->db->fetchAllAssociative(
            "SELECT a.*, u.prenom, u.nom, ta.libelle AS type_label
             FROM absence a
             JOIN utilisateur u ON u.id = a.utilisateur_id
             JOIN type_absence ta ON ta.id = a.type_absence_id
             ORDER BY a.date_debut"
        );

        foreach ($absences as $a) {
            $colorIdx = abs(crc32($a['nom'] . $a['prenom'])) % count($colors);
            
            $statusColor = match(strtolower($a['statut'])) {
                'approuvé' => '#10b981',
                'justifiée' => '#06b6d4',
                'en attente' => '#f59e0b',
                'refusé' => '#94a3b8',
                default => $colors[$colorIdx]
            };

            $events[] = [
                'id'        => 'a_' . $a['id'],
                'title'     => $a['prenom'] . ' ' . $a['nom'] . ' - Absence',
                'start'     => $a['date_debut'],
                'end'       => date('Y-m-d', strtotime($a['date_fin'] . ' +1 day')),
                'color'     => $statusColor,
                'extendedProps' => [
                    'type'   => $a['type_label'],
                    'statut' => $a['statut'],
                ],
            ];
        }

        return new JsonResponse($events);
    }

    // ══════════════════════════════════════════════════════════
    //  REQUÊTES PRIVÉES
    // ══════════════════════════════════════════════════════════

    private function getStatsValidations(): array
    {
        $toutes     = $this->db->fetchAllAssociative("SELECT statut FROM absence");
        $total      = count($toutes);
        $enAttente  = count(array_filter($toutes, fn($a) => strtolower($a['statut']) === 'en attente'));
        $approuvees = count(array_filter($toutes, fn($a) => strtolower($a['statut']) === 'approuvé'));
        $taux       = $total > 0 ? (int) round($enAttente / $total * 100) : 0;

        return [
            'en_attente'  => $enAttente,
            'approuvees'  => $approuvees,
            'taux_absence' => $taux,
        ];
    }

    private function getAbsencesEnAttente(): array
    {
        return $this->db->fetchAllAssociative(
            "SELECT a.*,
                    u.prenom, u.nom, u.posteActuel, u.departement,
                    ta.libelle AS type_label
             FROM absence a
             JOIN utilisateur u  ON u.id  = a.utilisateur_id
             JOIN type_absence ta ON ta.id = a.type_absence_id
             WHERE a.statut = 'En attente'
             ORDER BY a.date_debut ASC"
        );
    }

    private function getPlanning(): array
    {
        return $this->db->fetchAllAssociative(
            "SELECT c.*,
                    u.prenom, u.nom, u.posteActuel, u.departement,
                    tc.libelle AS type_label
             FROM conge c
             JOIN utilisateur u  ON u.id  = c.utilisateur_id
             JOIN type_conge tc  ON tc.id = c.type_conge_id
             ORDER BY c.date_debut DESC
             LIMIT 20"
        );
    }

    private function getMembres(): array
    {
        return $this->db->fetchAllAssociative(
            "SELECT id, prenom, nom FROM utilisateur WHERE statut = 'Actif' ORDER BY prenom"
        );
    }

    private function getCongesApprouves(): array
    {
        return $this->db->fetchAllAssociative(
            "SELECT c.*, u.prenom, u.nom, tc.libelle AS type_label
             FROM conge c
             JOIN utilisateur u  ON u.id  = c.utilisateur_id
             JOIN type_conge tc  ON tc.id = c.type_conge_id
             WHERE c.statut = 'Approuvé'"
        );
    }

    private function calculerSoldes(): array
    {
        $quota = self::QUOTA_ANNUEL_CP;

        $congesApprouves = $this->db->fetchAllAssociative(
            "SELECT c.type_conge_id, SUM(c.nbr_jours) AS total
             FROM conge c WHERE c.statut = 'Approuvé'
             GROUP BY c.type_conge_id"
        );
        $joursParTypeConge = [];
        foreach ($congesApprouves as $row) {
            $joursParTypeConge[$row['type_conge_id']] = (int)$row['total'];
        }

        $congesAttente = $this->db->fetchAllAssociative(
            "SELECT c.type_conge_id, SUM(c.nbr_jours) AS total
             FROM conge c WHERE c.statut = 'En attente'
             GROUP BY c.type_conge_id"
        );
        $attenteParType = [];
        foreach ($congesAttente as $row) {
            $attenteParType[$row['type_conge_id']] = (int)$row['total'];
        }

        $absApprouves = $this->db->fetchAllAssociative(
            "SELECT a.type_absence_id, SUM(a.nbr_jours) AS total
             FROM absence a WHERE a.statut IN ('Approuvé','Justifiée')
             GROUP BY a.type_absence_id"
        );
        $joursParTypeAbs = [];
        foreach ($absApprouves as $row) {
            $joursParTypeAbs[$row['type_absence_id']] = (int)$row['total'];
        }

        $totalCongesApprouves = array_sum($joursParTypeConge);
        $totalCongesAttente   = array_sum($attenteParType);
        $joursCongesMaladie   = $joursParTypeConge[2] ?? 0;
        $joursCongesExcep     = $joursParTypeConge[5] ?? 0;
        $joursCongesPat       = $joursParTypeConge[8] ?? 0;

        $restantCP    = max(0, $quota - $totalCongesApprouves);
        $pctConsomme  = (int) round($totalCongesApprouves / self::QUOTA_ANNUEL_CP * 100);

        $joursAbsJust   = $joursParTypeAbs[1] ?? 0;
        $joursAbsInjust = $joursParTypeAbs[2] ?? 0;
        $joursAbsMed    = $joursParTypeAbs[3] ?? 0;
        $totalAbsences  = $joursAbsJust + $joursAbsInjust + $joursAbsMed;
        $totalGlobal    = $totalCongesApprouves + $totalAbsences;

        $pieData = [];
        if ($restantCP > 0)         $pieData[] = ['label' => 'Solde restant',        'valeur' => $restantCP,        'color' => '#10b981'];
        if ($totalCongesApprouves > 0) $pieData[] = ['label' => 'Congés pris',        'valeur' => $totalCongesApprouves, 'color' => '#667eea'];
        if ($joursAbsJust > 0)      $pieData[] = ['label' => 'Abs. justifiées',       'valeur' => $joursAbsJust,     'color' => '#06b6d4'];
        if ($joursAbsInjust > 0)    $pieData[] = ['label' => 'Abs. injustifiées',     'valeur' => $joursAbsInjust,   'color' => '#ef4444'];
        if ($joursAbsMed > 0)       $pieData[] = ['label' => 'Abs. médicales',        'valeur' => $joursAbsMed,      'color' => '#8b5cf6'];
        if (empty($pieData))        $pieData[] = ['label' => 'Solde restant',         'valeur' => $quota,            'color' => '#10b981'];

        return [
            'quota'                  => $quota,
            'restant_cp'             => $restantCP,
            'total_conges_approuves' => $totalCongesApprouves,
            'total_conges_attente'   => $totalCongesAttente,
            'conges_maladie'         => $joursCongesMaladie,
            'conges_excep'           => $joursCongesExcep,
            'conges_pat'             => $joursCongesPat,
            'pct_consomme'           => $pctConsomme,
            'jours_abs_just'         => $joursAbsJust,
            'jours_abs_injust'       => $joursAbsInjust,
            'jours_abs_med'          => $joursAbsMed,
            'total_absences'         => $totalAbsences,
            'total_global'           => $totalGlobal,
            'pie_data'               => $pieData,
            'jours_par_type_conge'   => $joursParTypeConge,
            'attente_par_type'       => $attenteParType,
            'jours_par_type_abs'     => $joursParTypeAbs,
        ];
    }

    private function getTypesConge(): array
    {
        return $this->db->fetchAllAssociative("SELECT * FROM type_conge ORDER BY id");
    }

    private function getTypesAbsence(): array
    {
        return $this->db->fetchAllAssociative("SELECT * FROM type_absence ORDER BY id");
    }

    private function getJoursFeriesTunisie2026(): array
    {
        return [
            ['icone' => '🎉', 'nom' => "Jour de l'An",                  'date' => '2026-01-01', 'type' => 'Officiel',  'color' => '#3b82f6'],
            ['icone' => '✊', 'nom' => 'Fête de la Révolution',          'date' => '2026-01-14', 'type' => 'National',  'color' => '#ef4444'],
            ['icone' => '🌸', 'nom' => "Fête de l'Indépendance",         'date' => '2026-03-20', 'type' => 'National',  'color' => '#10b981'],
            ['icone' => '👨', 'nom' => 'Fête de la Jeunesse',            'date' => '2026-03-21', 'type' => 'Officiel',  'color' => '#f59e0b'],
            ['icone' => '🕌', 'nom' => 'Aïd el-Fitr',                   'date' => '2026-03-30', 'type' => 'Religieux', 'color' => '#10b981'],
            ['icone' => '🕌', 'nom' => 'Aïd el-Fitr (2ème jour)',        'date' => '2026-03-31', 'type' => 'Religieux', 'color' => '#10b981'],
            ['icone' => '🎖️', 'nom' => 'Fête des Martyrs',               'date' => '2026-04-09', 'type' => 'National',  'color' => '#7c3aed'],
            ['icone' => '🛠️', 'nom' => 'Fête du Travail',                 'date' => '2026-05-01', 'type' => 'Officiel',  'color' => '#ef4444'],
            ['icone' => '🐑', 'nom' => 'Aïd el-Adha',                   'date' => '2026-06-06', 'type' => 'Religieux', 'color' => '#f59e0b'],
            ['icone' => '🐑', 'nom' => 'Aïd el-Adha (2ème jour)',        'date' => '2026-06-07', 'type' => 'Religieux', 'color' => '#f59e0b'],
            ['icone' => '📅', 'nom' => 'Nouvel An Hégire',               'date' => '2026-07-18', 'type' => 'Religieux', 'color' => '#8b5cf6'],
            ['icone' => '🇹🇳', 'nom' => 'Fête de la République',          'date' => '2026-07-25', 'type' => 'National',  'color' => '#10b981'],
            ['icone' => '👩', 'nom' => 'Fête de la Femme',               'date' => '2026-08-13', 'type' => 'National',  'color' => '#ec4899'],
            ['icone' => '🌙', 'nom' => 'Mawlid (Naissance du Prophète)', 'date' => '2026-09-27', 'type' => 'Religieux', 'color' => '#06b6d4'],
            ['icone' => '🌊', 'nom' => "Fête de l'Évacuation",           'date' => '2026-10-15', 'type' => 'National',  'color' => '#06b6d4'],
        ];
    }
}