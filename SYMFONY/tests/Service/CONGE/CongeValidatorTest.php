<?php
// tests/Service/CONGE/CongeValidatorTest.php

namespace App\Tests\Service\CONGE;

use App\Entity\Conge;
use App\Service\CONGE\CongeValidator;
use PHPUnit\Framework\TestCase;

class CongeValidatorTest extends TestCase
{
    private CongeValidator $validator;
    private \DateTime $today;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new CongeValidator();
        $this->today = new \DateTime('2026-05-15'); // Date fixe pour les tests
    }

    /**
     * Test 1: Un congé valide doit passer la validation
     */
    public function testValidConge(): void
    {
        $conge = $this->createValidConge();
        
        $result = $this->validator->validate($conge, $this->today);
        
        $this->assertTrue($result);
    }

    /**
     * Test 2: La date de début ne peut pas être dans le passé
     */
    public function testDateDebutInPast(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La date de début ne peut pas être antérieure à aujourd\'hui.');
        
        $conge = $this->createValidConge();
        $conge->setDateDebut(new \DateTime('2026-05-14')); // Hier
        $conge->setDateFin(new \DateTime('2026-05-20'));
        
        $this->validator->validate($conge, $this->today);
    }

    /**
     * Test 3: La date de fin ne peut pas être avant la date de début
     */
    public function testDateFinBeforeDateDebut(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La date de fin doit être postérieure à la date de début.');
        
        $conge = $this->createValidConge();
        $conge->setDateDebut(new \DateTime('2026-05-20'));
        $conge->setDateFin(new \DateTime('2026-05-15'));
        
        $this->validator->validate($conge, $this->today);
    }

    /**
     * Test 4: Le nombre de jours doit être positif
     */
    public function testNbrJoursNegatif(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le nombre de jours doit être supérieur à 0.');
        
        $conge = $this->createValidConge();
        $conge->setNbrJours(0);
        
        $this->validator->validate($conge, $this->today);
    }

    /**
     * Test 5: Le nombre de jours ne peut pas dépasser 30
     */
    public function testNbrJoursDepasse30(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le nombre de jours ne peut pas dépasser 30.');
        
        $conge = $this->createValidConge();
        $conge->setNbrJours(31);
        
        $this->validator->validate($conge, $this->today);
    }

    /**
     * Test 6: Le nombre de jours valide (entre 1 et 30)
     */
    public function testNbrJoursValide(): void
    {
        $conge = $this->createValidConge();
        
        // Test avec 1 jour
        $conge->setNbrJours(1);
        $result = $this->validator->validate($conge, $this->today);
        $this->assertTrue($result);
        
        // Test avec 15 jours
        $conge->setNbrJours(15);
        $result = $this->validator->validate($conge, $this->today);
        $this->assertTrue($result);
        
        // Test avec 30 jours
        $conge->setNbrJours(30);
        $result = $this->validator->validate($conge, $this->today);
        $this->assertTrue($result);
    }

    /**
     * Test 7: Le statut ne peut pas être vide
     */
    public function testStatutVide(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le statut est obligatoire.');
        
        $conge = $this->createValidConge();
        $conge->setStatut('');
        
        $this->validator->validate($conge, $this->today);
    }

    /**
     * Test 8: Le statut null est invalide
     */
    public function testStatutNull(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le statut est obligatoire.');
        
        $conge = $this->createValidConge();
        $conge->setStatut(null);
        
        $this->validator->validate($conge, $this->today);
    }

    /**
     * Test 9: Le statut valide (En attente, Approuvé, Refusé)
     */
    public function testStatutValide(): void
    {
        $conge = $this->createValidConge();
        
        $statutsValides = ['En attente', 'Approuvé', 'Refusé'];
        
        foreach ($statutsValides as $statut) {
            $conge->setStatut($statut);
            $result = $this->validator->validate($conge, $this->today);
            $this->assertTrue($result);
        }
    }

    /**
     * Test 10: Le type de congé ne peut pas être vide
     */
    public function testTypeCongeVide(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le type de congé est obligatoire.');
        
        $conge = $this->createValidConge();
        $conge->setTypeCongeId(null);
        
        $this->validator->validate($conge, $this->today);
    }

    /**
     * Test 11: Le type de congé doit être valide (ID > 0)
     */
    public function testTypeCongeValide(): void
    {
        $conge = $this->createValidConge();
        
        $typesValides = [1, 2, 3, 5, 8];
        
        foreach ($typesValides as $typeId) {
            $conge->setTypeCongeId($typeId);
            $result = $this->validator->validate($conge, $this->today);
            $this->assertTrue($result);
        }
    }

    /**
     * Test 12: L'utilisateur ne peut pas être vide
     */
    public function testUtilisateurVide(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('L\'utilisateur est obligatoire.');
        
        $conge = $this->createValidConge();
        $conge->setUtilisateurId(null);
        
        $this->validator->validate($conge, $this->today);
    }

    /**
     * Test 13: L'utilisateur doit être valide (ID > 0)
     */
    public function testUtilisateurValide(): void
    {
        $conge = $this->createValidConge();
        
        $conge->setUtilisateurId(1);
        $result = $this->validator->validate($conge, $this->today);
        $this->assertTrue($result);
        
        $conge->setUtilisateurId(999);
        $result = $this->validator->validate($conge, $this->today);
        $this->assertTrue($result);
    }

    /**
     * Test 14: Calcul du nombre de jours entre deux dates
     */
    public function testCalculerNbrJours(): void
    {
        $conge = new Conge();
        
        // Test 1: 10 → 15 = 6 jours
        $conge->setDateDebut(new \DateTime('2026-05-10'));
        $conge->setDateFin(new \DateTime('2026-05-15'));
        $nbrJours = $this->validator->calculerNbrJours($conge);
        $this->assertEquals(6, $nbrJours);
        
        // Test 2: Même jour = 1 jour
        $conge->setDateDebut(new \DateTime('2026-05-10'));
        $conge->setDateFin(new \DateTime('2026-05-10'));
        $nbrJours = $this->validator->calculerNbrJours($conge);
        $this->assertEquals(1, $nbrJours);
        
        // Test 3: 1 → 31 = 31 jours
        $conge->setDateDebut(new \DateTime('2026-05-01'));
        $conge->setDateFin(new \DateTime('2026-05-31'));
        $nbrJours = $this->validator->calculerNbrJours($conge);
        $this->assertEquals(31, $nbrJours);
        
        // Test 4: Sur deux mois
        $conge->setDateDebut(new \DateTime('2026-05-25'));
        $conge->setDateFin(new \DateTime('2026-06-05'));
        $nbrJours = $this->validator->calculerNbrJours($conge);
        $this->assertEquals(12, $nbrJours);
    }

    /**
     * Test 15: Date aujourd'hui est valide
     */
    public function testDateAujourdhui(): void
    {
        $conge = $this->createValidConge();
        $conge->setDateDebut($this->today);
        $conge->setDateFin($this->today);
        
        $result = $this->validator->validate($conge, $this->today);
        
        $this->assertTrue($result);
    }

    /**
     * Test 16: Date future est valide
     */
    public function testDateFuture(): void
    {
        $conge = $this->createValidConge();
        $conge->setDateDebut(new \DateTime('2026-05-20'));
        $conge->setDateFin(new \DateTime('2026-05-25'));
        
        $result = $this->validator->validate($conge, $this->today);
        
        $this->assertTrue($result);
    }

    /**
     * Crée un congé valide pour les tests
     */
    private function createValidConge(): Conge
    {
        $conge = new Conge();
        $conge->setDateDebut(new \DateTime('2026-05-20'));
        $conge->setDateFin(new \DateTime('2026-05-25'));
        $conge->setNbrJours(6);
        $conge->setStatut('En attente');
        $conge->setTypeCongeId(2);
        $conge->setUtilisateurId(1);
        
        return $conge;
    }
}