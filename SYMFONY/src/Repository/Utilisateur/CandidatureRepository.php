<?php

namespace App\Repository\Utilisateur;

use App\Entity\Candidature;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CandidatureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Candidature::class);
    }

    /**
     * Returns accepted candidatures with a flag indicating whether the
     * candidature was already converted to an employee account.
     * Mirrors Java CandidatureService::getAcceptedCandidatures().
     */
    public function findAcceptedWithConversionFlag(): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = <<<SQL
            SELECT c.*,
                   CASE WHEN u.id IS NOT NULL THEN 1 ELSE 0 END AS deja_converti
            FROM candidature c
            LEFT JOIN utilisateur u
                   ON c.employe_id = u.id
                  AND c.employe_id IS NOT NULL
                  AND c.employe_id > 0
            WHERE c.statut = 'Accepté'
            ORDER BY c.date_depot DESC
            LIMIT 90
        SQL;

        $rows = $conn->fetchAllAssociative($sql);
        $result = [];

        foreach ($rows as $row) {
            $c = new Candidature();
            $c->setNom($row['nom'] ?? null);
            $c->setPrenom($row['prenom'] ?? null);
            $c->setEmail($row['email'] ?? null);
            $c->setCandidatId($row['candidat_id'] ?? null);
            $empId = $row['employe_id'] ?? null;
            $c->setEmployeId(($empId !== null && (int)$empId > 0) ? (int)$empId : null);
            $c->setTypeCandidat($row['type_candidat'] ?? null);
            $c->setStatut($row['statut'] ?? null);
            $c->setEtapePipeline($row['etape_pipeline'] ?? null);
            $c->setScoringIa(isset($row['scoring_ia']) ? (float)$row['scoring_ia'] : null);
            $c->setCvUrl($row['cv_url'] ?? null);
            $c->setLettreMotivationUrl($row['lettre_motivation_url'] ?? null);
            $c->setSalairePretendu(isset($row['salaire_pretendu']) ? (float)$row['salaire_pretendu'] : null);
            $c->setCommentairesRh($row['commentaires_rh'] ?? null);
            if (!empty($row['date_depot'])) {
                $c->setDateDepot(new \DateTime($row['date_depot']));
            }
            // Reflection hack to set the private id (no setter for DB id)
            $ref = new \ReflectionProperty(Candidature::class, 'id');
            $ref->setAccessible(true);
            $ref->setValue($c, (int)$row['id']);

            $c->setAlreadyConverted((bool)$row['deja_converti']);
            $result[] = $c;
        }

        return $result;
    }

    public function isDejaConverti(int $candidatureId): bool
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = <<<SQL
            SELECT COUNT(*) FROM candidature c
            JOIN utilisateur u ON c.employe_id = u.id
            WHERE c.id = :id
              AND c.employe_id IS NOT NULL
              AND c.employe_id > 0
        SQL;
        return (int)$conn->fetchOne($sql, ['id' => $candidatureId]) > 0;
    }

    public function setEmployeId(int $candidatureId, int $employeId): void
    {
        $conn = $this->getEntityManager()->getConnection();
        $conn->executeStatement(
            'UPDATE candidature SET employe_id = ? WHERE id = ?',
            [$employeId, $candidatureId]
        );
    }

    public function save(Candidature $candidature, bool $flush = true): void
    {
        $this->getEntityManager()->persist($candidature);
        if ($flush) $this->getEntityManager()->flush();
    }
}