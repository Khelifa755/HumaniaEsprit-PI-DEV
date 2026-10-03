<?php

namespace App\Tests\Service\RECRUTEMENT;

use App\Entity\Poste_externe;
use App\Service\RECRUTEMENT\PosteExterneManager;
use PHPUnit\Framework\TestCase;

class PosteExterneManagerTest extends TestCase
{
    // ── Test 1 : Poste valide ────────────────────────────────────────────
    public function testPosteValide(): void
    {
        $poste = new Poste_externe();
        $poste->setTitre('Développeur Symfony');
        $poste->setSalaire(2500.0);
        $poste->setDatePublication(new \DateTimeImmutable('2025-01-01'));
        $poste->setDateCloture(new \DateTimeImmutable('2025-03-01'));
        $poste->setNombreEmploye(2);
        $poste->setStatut('Ouvert');

        $manager = new PosteExterneManager();

        $this->assertTrue($manager->validate($poste));
    }

    // ── Test 2 : Titre manquant ──────────────────────────────────────────
    public function testPosteSansTitre(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le titre du poste est obligatoire.');

        $poste = new Poste_externe();
        $poste->setTitre('');
        $poste->setSalaire(2500.0);
        $poste->setDatePublication(new \DateTimeImmutable('2025-01-01'));
        $poste->setDateCloture(new \DateTimeImmutable('2025-03-01'));
        $poste->setNombreEmploye(2);
        $poste->setStatut('Ouvert');

        $manager = new PosteExterneManager();
        $manager->validate($poste);
    }

    // ── Test 3 : Salaire invalide (= 0) ─────────────────────────────────
    public function testPosteSalaireZero(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le salaire doit être supérieur à 0.');

        $poste = new Poste_externe();
        $poste->setTitre('Développeur Symfony');
        $poste->setSalaire(0.0);
        $poste->setDatePublication(new \DateTimeImmutable('2025-01-01'));
        $poste->setDateCloture(new \DateTimeImmutable('2025-03-01'));
        $poste->setNombreEmploye(2);
        $poste->setStatut('Ouvert');

        $manager = new PosteExterneManager();
        $manager->validate($poste);
    }

    // ── Test 4 : Salaire négatif ─────────────────────────────────────────
    public function testPosteSalaireNegatif(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le salaire doit être supérieur à 0.');

        $poste = new Poste_externe();
        $poste->setTitre('Développeur Symfony');
        $poste->setSalaire(-500.0);
        $poste->setDatePublication(new \DateTimeImmutable('2025-01-01'));
        $poste->setDateCloture(new \DateTimeImmutable('2025-03-01'));
        $poste->setNombreEmploye(2);
        $poste->setStatut('Ouvert');

        $manager = new PosteExterneManager();
        $manager->validate($poste);
    }

    // ── Test 5 : Date de clôture avant date de publication ───────────────
    public function testPosteDateClotureInvalide(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La date de clôture doit être postérieure à la date de publication.');

        $poste = new Poste_externe();
        $poste->setTitre('Développeur Symfony');
        $poste->setSalaire(2500.0);
        $poste->setDatePublication(new \DateTimeImmutable('2025-03-01'));
        $poste->setDateCloture(new \DateTimeImmutable('2025-01-01'));
        $poste->setNombreEmploye(2);
        $poste->setStatut('Ouvert');

        $manager = new PosteExterneManager();
        $manager->validate($poste);
    }

    // ── Test 6 : Nombre d'employés invalide (= 0) ────────────────────────
    public function testPosteNombreEmployeZero(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le nombre d\'employés doit être au moins 1.');

        $poste = new Poste_externe();
        $poste->setTitre('Développeur Symfony');
        $poste->setSalaire(2500.0);
        $poste->setDatePublication(new \DateTimeImmutable('2025-01-01'));
        $poste->setDateCloture(new \DateTimeImmutable('2025-03-01'));
        $poste->setNombreEmploye(0);
        $poste->setStatut('Ouvert');

        $manager = new PosteExterneManager();
        $manager->validate($poste);
    }

    // ── Test 7 : Statut invalide ─────────────────────────────────────────
    public function testPosteStatutInvalide(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le statut doit être : Ouvert, Fermé ou En attente.');

        $poste = new Poste_externe();
        $poste->setTitre('Développeur Symfony');
        $poste->setSalaire(2500.0);
        $poste->setDatePublication(new \DateTimeImmutable('2025-01-01'));
        $poste->setDateCloture(new \DateTimeImmutable('2025-03-01'));
        $poste->setNombreEmploye(2);
        $poste->setStatut('StatutInvalide');

        $manager = new PosteExterneManager();
        $manager->validate($poste);
    }
}