<?php

namespace App\Repository\Utilisateur;

use App\Entity\Utilisateur;
use App\Enum\Role;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UtilisateurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Utilisateur::class);
    }

    // ── Login ─────────────────────────────────────────────────────────────────

    public function findByEmailAndPassword(string $email, string $hashedPassword): ?Utilisateur
    {
        return $this->createQueryBuilder('u')
            ->where('u.email = :email AND u.motDePasse = :pwd')
            ->setParameter('email', $email)
            ->setParameter('pwd', $hashedPassword)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByEmail(string $email): ?Utilisateur
    {
        return $this->findOneBy(['email' => $email]);
    }

    // ── Existence checks ──────────────────────────────────────────────────────

    public function emailExists(string $email): bool
    {
        return (int) $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u.email = :email')
            ->setParameter('email', $email)
            ->getQuery()->getSingleScalarResult() > 0;
    }

    public function emailExistsForOther(int $excludeId, string $email): bool
    {
        return (int) $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u.email = :email AND u.id != :id')
            ->setParameter('email', $email)
            ->setParameter('id', $excludeId)
            ->getQuery()->getSingleScalarResult() > 0;
    }

    public function usernameExists(string $username): bool
    {
        return (int) $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u.username = :username')
            ->setParameter('username', $username)
            ->getQuery()->getSingleScalarResult() > 0;
    }

    // ── Lists ─────────────────────────────────────────────────────────────────

    /** All users including archived. */
    public function findAll(): array
    {
        return $this->createQueryBuilder('u')
            ->orderBy('u.dateCreation', 'DESC')
            ->setMaxResults(90)
            ->getQuery()->getResult();
    }

    /** Active users (not archived). */
    public function findActifs(): array
    {
        return $this->createQueryBuilder('u')
            ->where("u.statut IS NULL OR UPPER(TRIM(u.statut)) != 'ARCHIVE'")
            ->orderBy('u.nom', 'ASC')
            ->setMaxResults(90)
            ->getQuery()->getResult();
    }

    /** Archived users only. */
    public function findArchives(): array
    {
        return $this->createQueryBuilder('u')
            ->where("UPPER(TRIM(u.statut)) = 'ARCHIVE'")
            ->orderBy('u.nom', 'ASC')
            ->setMaxResults(90)
            ->getQuery()->getResult();
    }

    /** Non-archived managers. */
    public function findManagers(): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.roleValue = :role')
            ->andWhere("UPPER(TRIM(COALESCE(u.statut, ''))) != 'ARCHIVE'")
            ->setParameter('role', Role::MANAGER->value)
            ->orderBy('u.nom', 'ASC')
            ->addOrderBy('u.prenom', 'ASC')
            ->setMaxResults(90)
            ->getQuery()->getResult();
    }

    // ── Stats ─────────────────────────────────────────────────────────────────

    public function countAll(): int
    {
        return (int) $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->getQuery()->getSingleScalarResult();
    }

    public function countByStatut(string $statut): int
    {
        return (int) $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('UPPER(TRIM(u.statut)) = :statut')
            ->setParameter('statut', strtoupper($statut))
            ->getQuery()->getSingleScalarResult();
    }

    // ── Username generator ────────────────────────────────────────────────────

    public function generateUniqueUsername(string $nom, string $prenom): string
    {
        $base = strtolower(preg_replace('/[^a-z0-9]/i', '', $prenom . $nom));
        if ($base === '') $base = 'user';
        $candidate = $base;
        $suffix = 1;
        while ($this->usernameExists($candidate)) {
            $candidate = $base . $suffix++;
        }
        return $candidate;
    }

    // ── Persist / Remove ─────────────────────────────────────────────────────

    public function save(Utilisateur $utilisateur, bool $flush = true): void
    {
        $this->getEntityManager()->persist($utilisateur);
        if ($flush) $this->getEntityManager()->flush();
    }

    public function remove(Utilisateur $utilisateur, bool $flush = true): void
    {
        $this->getEntityManager()->remove($utilisateur);
        if ($flush) $this->getEntityManager()->flush();
    }
}