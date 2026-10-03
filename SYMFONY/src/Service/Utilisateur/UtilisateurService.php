<?php

namespace App\Service\Utilisateur;

use App\Entity\Utilisateur;
use App\Enum\Role;
use App\Repository\Utilisateur\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Mirrors Java UtilisateurService (without face recognition).
 * Password hashing: SHA-256 hex for Java DB compatibility.
 */
class UtilisateurService
{
    private UtilisateurRepository $repository;

    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
        $this->repository = $em->getRepository(Utilisateur::class);
    }

    // ── Password ──────────────────────────────────────────────────────────────

    public function hashPassword(string $password): string
    {
        return hash('sha256', $password);
    }

    // ── Presence ──────────────────────────────────────────────────────────────

    public function setOnline(int $userId, bool $online): void
    {
        $u = $this->repository->find($userId);
        if (!$u) return;
        $u->setIsOnline($online);
        $u->setLastSeen(new \DateTime());
        $this->em->flush();
    }

    // ── Existence checks ──────────────────────────────────────────────────────

    public function emailExiste(string $email): bool
    {
        return $this->repository->emailExists($email);
    }

    public function emailExistePourAutre(int $excludeId, string $email): bool
    {
        return $this->repository->emailExistsForOther($excludeId, $email);
    }

    public function usernameExiste(string $username): bool
    {
        return $this->repository->usernameExists($username);
    }

    // ── Insert ────────────────────────────────────────────────────────────────

    public function inserer(Utilisateur $u): void
    {
        $u->setMotDePasse($this->hashPassword($u->getMotDePasse() ?? ''));
        if (!$u->getStatut()) $u->setStatut('Actif');
        $this->repository->save($u);
    }

    public function insererEtRetournerId(Utilisateur $u): int
    {
        $this->inserer($u);
        return $u->getId();
    }

    // ── Conversion candidat → utilisateur ─────────────────────────────────────

    /**
     * @return array{userId: int, rawPassword: string, loginEmail: string}
     */
    public function convertirCandidatEnUtilisateur(
        Utilisateur $u,
        int $candidatureId,
        string $candidatEmail,
        ?int $managerId,
        ?string $specialite,
        ?string $matricule,
        ?string $posteActuel,
        ?string $departement,
    ): array {
        if (empty($candidatEmail)) {
            throw new \RuntimeException("L'email du candidat est requis.");
        }
        if ($this->emailExiste($candidatEmail)) {
            throw new \RuntimeException("Un compte existe déjà avec l'adresse : $candidatEmail");
        }

        $username = $u->getUsername();
        if (!$username || $this->usernameExiste($username)) {
            $username = $this->repository->generateUniqueUsername(
                $u->getNom() ?? '', $u->getPrenom() ?? ''
            );
        }

        $u->setUsername($username);
        $u->setEmail($candidatEmail);
        $u->setManagerId($managerId);
        $u->setMatricule($matricule ?: null);
        $u->setPosteActuel($posteActuel ?: null);
        $u->setDepartement($departement ?: null);

        $rawPassword = $this->genererMotDePasseSecurise();
        $u->setMotDePasse($rawPassword);

        $this->em->beginTransaction();
        try {
            $userId = $this->insererEtRetournerId($u);
            $this->em->getConnection()->executeStatement(
                'UPDATE candidature SET employe_id = ? WHERE id = ?',
                [$userId, $candidatureId]
            );
            $this->em->commit();
            return ['userId' => $userId, 'rawPassword' => $rawPassword, 'loginEmail' => $candidatEmail];
        } catch (\Throwable $e) {
            $this->em->rollback();
            throw $e;
        }
    }

    // ── Update ────────────────────────────────────────────────────────────────

    public function modifier(Utilisateur $u): void
    {
        $this->em->flush();
    }

    public function modifierAvecMotDePasse(Utilisateur $u, ?string $nouveauMotDePasse): void
    {
        if ($nouveauMotDePasse && trim($nouveauMotDePasse) !== '') {
            $u->setMotDePasse($this->hashPassword($nouveauMotDePasse));
        }
        $this->em->flush();
    }

    public function modifierProfil(Utilisateur $u, ?string $nouveauMotDePasse): void
    {
        if ($nouveauMotDePasse && trim($nouveauMotDePasse) !== '') {
            $u->setMotDePasse($this->hashPassword($nouveauMotDePasse));
        }
        // The current authenticated user is already managed by Doctrine.
        $this->em->persist($u);
        $this->em->flush();
    }

    // ── Delete / Archive ──────────────────────────────────────────────────────

    public function supprimer(Utilisateur $u): void
    {
        $this->repository->remove($u);
    }

    public function archiverUtilisateur(Utilisateur $u): void
    {
        $u->setStatut('Archive');
        $this->em->flush();
    }

    public function restaurerUtilisateur(Utilisateur $u): void
    {
        $u->setStatut('Actif');
        $this->em->flush();
    }

    // ── Read ──────────────────────────────────────────────────────────────────

    public function recupererTous(): array   { return $this->repository->findAll(); }
    public function recupererActifs(): array  { return $this->repository->findActifs(); }
    public function recupererArchives(): array { return $this->repository->findArchives(); }
    public function getManagers(): array      { return $this->repository->findManagers(); }

    // ── MFA ───────────────────────────────────────────────────────────────────

    public function activerMfa(Utilisateur $u, string $secret): void
    {
        $u->setMfaSecret($secret);
        $u->setMfaEnabled(true);
        $this->em->flush();
    }

    public function desactiverMfa(Utilisateur $u): void
    {
        $u->setMfaSecret(null);
        $u->setMfaEnabled(false);
        $this->em->flush();
    }

    // ── Forgot password ───────────────────────────────────────────────────────

    public function reinitialiserMotDePasse(string $email, string $newPassword): void
    {
        $u = $this->repository->findByEmail($email);
        if (!$u) throw new \RuntimeException("Aucun compte trouvé pour : $email");
        $u->setMotDePasse($this->hashPassword($newPassword));
        $this->em->flush();
    }

    // ── Google login ──────────────────────────────────────────────────────────

    public function connecterViaGoogle(string $email): Utilisateur
    {
        $u = $this->repository->findByEmail($email);
        
        // If user doesn't exist, auto-create with EMPLOYE role
        if (!$u) {
            $u = new Utilisateur();
            $u->setEmail($email);
            $u->setUsername(explode('@', $email)[0]); // Use email prefix as username
            $u->setRole(Role::EMPLOYE);
            $u->setStatut('Actif');
            // No password for OAuth users
            $u->setMotDePasse(null);
            
            $this->em->persist($u);
            $this->em->flush();
            
            return $u;
        }
        
        $statut = strtolower(trim($u->getStatut() ?? ''));
        if ($statut === 'archive')  throw new \RuntimeException("Ce compte est archivé.");
        if ($statut === 'bloque')   throw new \RuntimeException("Ce compte est bloqué.");
        if ($statut === 'suspendu') throw new \RuntimeException("Ce compte est suspendu.");
        return $u;
    }

    // ── Stats ─────────────────────────────────────────────────────────────────

    /**
     * @return array<string, mixed>
     */
    public function getStats(?Utilisateur $viewer = null): array
    {
        $actifs = $this->repository->countByStatut('Actif');
        $archives = $this->repository->countByStatut('Archivé');
        $total = $actifs + $archives;
        
        // Count accepted candidatures
        $conn = $this->em->getConnection();
        
        // User stats
        $acceptedCandidats = (int) $conn->fetchOne(
            "SELECT COUNT(*) as cnt FROM candidature WHERE statut = 'Accepté' AND employe_id IS NULL"
        );
        
        // Candidature stats by status
        $candidatureByStatus = $conn->fetchAllAssociative(
            "SELECT statut, COUNT(*) as cnt FROM candidature WHERE statut IS NOT NULL AND statut != '' GROUP BY statut"
        );
        $statusCounts = [];
        foreach ($candidatureByStatus as $row) {
            $statusCounts[$row['statut']] = (int) $row['cnt'];
        }
        
        // Candidature stats by pipeline stage
        $candidatureByStage = $conn->fetchAllAssociative(
            "SELECT etape_pipeline, COUNT(*) as cnt FROM candidature WHERE etape_pipeline IS NOT NULL AND etape_pipeline != '' GROUP BY etape_pipeline"
        );
        $stageCounts = [];
        foreach ($candidatureByStage as $row) {
            $stageCounts[$row['etape_pipeline']] = (int) $row['cnt'];
        }
        
        // Average scoring
        $avgScoring = $conn->fetchOne(
            "SELECT AVG(scoring_ia) as avg_score FROM candidature WHERE scoring_ia IS NOT NULL"
        );
        
        // Total candidatures
        $totalCandidatures = (int) $conn->fetchOne("SELECT COUNT(*) as cnt FROM candidature");
        
        // Recent submissions (last 7 days)
        $recentSubmissions = (int) $conn->fetchOne(
            "SELECT COUNT(*) as cnt FROM candidature WHERE date_depot >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
        );
        
        // Conversion rate (accepted / total * 100)
        $conversionRate = $totalCandidatures > 0 
            ? round(($statusCounts['Accepté'] ?? 0) / $totalCandidatures * 100, 1) 
            : 0;

        // ── Congés & absences (scope aligns with CongeController list when $viewer is set) ──
        $congeScope = $this->scopedUtilisateurCondition($viewer, 'c.utilisateur_id');
        $congeWhere = $congeScope['sql'];
        $congeParams = $congeScope['params'];

        $totalConges = (int) $conn->fetchOne(
            "SELECT COUNT(*) FROM conge c WHERE $congeWhere",
            $congeParams
        );
        $congesEnAttente = (int) $conn->fetchOne(
            "SELECT COUNT(*) FROM conge c WHERE $congeWhere AND c.statut = 'En attente'",
            $congeParams
        );
        $congesApprouves = (int) $conn->fetchOne(
            "SELECT COUNT(*) FROM conge c WHERE $congeWhere AND c.statut = 'Approuvé'",
            $congeParams
        );

        $absScope = $this->scopedUtilisateurCondition($viewer, 'a.utilisateur_id');
        $absWhere = $absScope['sql'];
        $absParams = $absScope['params'];
        $totalAbsences = (int) $conn->fetchOne(
            "SELECT COUNT(*) FROM absence a WHERE $absWhere
             AND YEAR(a.date_debut) = YEAR(CURDATE()) AND MONTH(a.date_debut) = MONTH(CURDATE())",
            $absParams
        );

        // ── Formations & compétences (global catalogue) ──
        $totalFormations = (int) ($conn->fetchOne('SELECT COUNT(*) FROM formation') ?: 0);
        $totalCompetences = (int) ($conn->fetchOne(
            "SELECT COUNT(*) FROM competence WHERE statutCompetence = 'ACTIF'"
        ) ?: 0);

        $competenceTypeRows = $conn->fetchAllAssociative(
            "SELECT typeCompetence, COUNT(*) AS cnt FROM competence WHERE statutCompetence = 'ACTIF' GROUP BY typeCompetence"
        );
        $competencesByType = [];
        foreach ($competenceTypeRows as $row) {
            $competencesByType[$row['typeCompetence']] = (int) $row['cnt'];
        }

        $totalInscriptions = (int) ($conn->fetchOne('SELECT COUNT(*) FROM inscriptionformation') ?: 0);
        $totalEvaluations = (int) ($conn->fetchOne('SELECT COUNT(*) FROM evaluationformation') ?: 0);
        $avgEvalScore = (float) ($conn->fetchOne(
            'SELECT AVG(score_pct) FROM resultatevaluation WHERE score_pct IS NOT NULL'
        ) ?: 0);
        $avgEvalScore = round($avgEvalScore, 1);

        $competencesValidees = (int) ($conn->fetchOne(
            'SELECT COUNT(*) FROM competenceemploye WHERE niveauValide = 1'
        ) ?: 0);
        $totalCompetencesEmployes = (int) ($conn->fetchOne(
            'SELECT COUNT(*) FROM competenceemploye'
        ) ?: 0);

        // ── Social & planning (global) ──
        $totalPublications = (int) ($conn->fetchOne(
            "SELECT COUNT(*) FROM publication WHERE statut = 'ACTIF'"
        ) ?: 0);
        $totalEvenements = (int) ($conn->fetchOne(
            "SELECT COUNT(*) FROM evenement WHERE dateEvenement >= CURDATE()"
        ) ?: 0);
        $totalNotifications = (int) ($conn->fetchOne(
            'SELECT COUNT(*) FROM notification'
        ) ?: 0);
        
        return [
            'total' => $total,
            'actifs' => $actifs,
            'archives' => $archives,
            'candidats' => $acceptedCandidats,
            'candidatureByStatus' => $statusCounts,
            'candidatureByStage' => $stageCounts,
            'totalCandidatures' => $totalCandidatures,
            'avgScoring' => round($avgScoring ?? 0, 2),
            'recentSubmissions' => $recentSubmissions,
            'conversionRate' => $conversionRate,
            'totalConges' => $totalConges,
            'congesEnAttente' => $congesEnAttente,
            'congesApprouves' => $congesApprouves,
            'totalAbsences' => $totalAbsences,
            'totalFormations' => $totalFormations,
            'totalCompetences' => $totalCompetences,
            'competencesByType' => $competencesByType,
            'totalInscriptions' => $totalInscriptions,
            'totalEvaluations' => $totalEvaluations,
            'avgEvalScore' => $avgEvalScore,
            'competencesValidees' => $competencesValidees,
            'totalCompetencesEmployes' => $totalCompetencesEmployes,
            'totalPublications' => $totalPublications,
            'totalEvenements' => $totalEvenements,
            'totalNotifications' => $totalNotifications,
        ];
    }

    /**
     * Restrict rows to the same population as CongeController / absence listings:
     * no viewer or ADMIN → all users; MANAGER → same department; otherwise → current user only.
     *
     * @return array{sql: string, params: array<int, mixed>}
     */
    private function scopedUtilisateurCondition(?Utilisateur $viewer, string $utilisateurColumnSql): array
    {
        if ($viewer === null) {
            return ['sql' => '1=1', 'params' => []];
        }

        $role = $viewer->getRole()?->value;
        if ($role === 'ADMIN') {
            return ['sql' => '1=1', 'params' => []];
        }

        if ($role === 'MANAGER') {
            $dept = $viewer->getDepartement();
            if ($dept === null || $dept === '') {
                return ['sql' => "$utilisateurColumnSql = ?", 'params' => [$viewer->getId()]];
            }

            return [
                'sql' => "$utilisateurColumnSql IN (SELECT u.id FROM utilisateur u WHERE u.departement = ?)",
                'params' => [$dept],
            ];
        }

        return ['sql' => "$utilisateurColumnSql = ?", 'params' => [$viewer->getId()]];
    }

    // ── Generators ───────────────────────────────────────────────────────────

    public function genererUsernameUnique(string $nom, string $prenom): string
    {
        return $this->repository->generateUniqueUsername($nom, $prenom);
    }

    public function genererMotDePasseSecurise(): string
    {
        $upper   = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lower   = 'abcdefghijklmnopqrstuvwxyz';
        $digits  = '0123456789';
        $symbols = '!@#$%^&*()-_=+[]{}';
        $all     = $upper . $lower . $digits . $symbols;

        $pwd  = $upper[random_int(0, strlen($upper) - 1)];
        $pwd .= $lower[random_int(0, strlen($lower) - 1)];
        $pwd .= $digits[random_int(0, strlen($digits) - 1)];
        $pwd .= $symbols[random_int(0, strlen($symbols) - 1)];
        for ($i = 4; $i < 12; $i++) $pwd .= $all[random_int(0, strlen($all) - 1)];

        $chars = str_split($pwd);
        shuffle($chars);
        return implode('', $chars);
    }
}