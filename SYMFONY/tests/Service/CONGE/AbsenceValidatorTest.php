<?php

namespace App\Tests\Service\CONGE;

use App\Entity\Absence;
use App\Entity\Utilisateur;
use App\Service\CONGE\AbsenceValidator;
use PHPUnit\Framework\TestCase;

class AbsenceValidatorTest extends TestCase
{
    private AbsenceValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new AbsenceValidator();
    }

    // ═══════════════════════════════════════════════════════════
    // TEST 1 : Absence valide avec toutes les données
    // ═══════════════════════════════════════════════════════════
    public function testValidAbsence(): void
    {
        $user = new Utilisateur();
        $user->setEmail('test@example.com');
        $user->setNom('Doe');
        $user->setPrenom('John');

        $absence = new Absence();
        $absence->setDateDebut(new \DateTime('+1 day'));
        $absence->setMotif('Rendez-vous médical');
        $absence->setDureeMinutes(120); // 2h
        $absence->setUtilisateurId(1);

        $result = $this->validator->validate($absence, $user);
        
        $this->assertTrue($result);
    }

    // ═══════════════════════════════════════════════════════════
    // TEST 2 : Date de début obligatoire
    // ═══════════════════════════════════════════════════════════
    public function testAbsenceWithoutDateDebut(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La date de début est obligatoire');

        $absence = new Absence();
        $absence->setMotif('Test');

        $this->validator->validateDateDebut($absence);
    }

    // ═══════════════════════════════════════════════════════════
    // TEST 3 : Durée minimale (30 minutes)
    // ═══════════════════════════════════════════════════════════
    public function testDureeTropCourte(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La durée minimale est de 30 minutes');

        $this->validator->validateDuree(20); // 20 minutes
    }

    // ═══════════════════════════════════════════════════════════
    // TEST 4 : Durée maximale (4 heures)
    // ═══════════════════════════════════════════════════════════
    public function testDureeTropLongue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La durée maximale est de 4 heures');

        $this->validator->validateDuree(300); // 5 heures
    }

    // ═══════════════════════════════════════════════════════════
    // TEST 5 : Durée valide
    // ═══════════════════════════════════════════════════════════
    public function testDureeValide(): void
    {
        $result = $this->validator->validateDuree(120); // 2 heures
        $this->assertTrue($result);
    }

    // ═══════════════════════════════════════════════════════════
    // TEST 6 : Date dans le passé
    // ═══════════════════════════════════════════════════════════
    public function testDateDansPasse(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La date ne peut pas être dans le passé');

        $datePassee = new \DateTime('-1 day');
        $this->validator->validateDateNotPast($datePassee);
    }

    // ═══════════════════════════════════════════════════════════
    // TEST 7 : Date valide (aujourd'hui)
    // ═══════════════════════════════════════════════════════════
    public function testDateAujourdhui(): void
    {
        $today = new \DateTime('today');
        $result = $this->validator->validateDateNotPast($today);
        $this->assertTrue($result);
    }

    // ═══════════════════════════════════════════════════════════
    // TEST 8 : Date valide (future)
    // ═══════════════════════════════════════════════════════════
    public function testDateFuture(): void
    {
        $dateFuture = new \DateTime('+5 days');
        $result = $this->validator->validateDateNotPast($dateFuture);
        $this->assertTrue($result);
    }

    // ═══════════════════════════════════════════════════════════
    // TEST 9 : Motif obligatoire
    // ═══════════════════════════════════════════════════════════
    public function testMotifVide(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le motif est obligatoire');

        $this->validator->validateMotif('');
    }

    // ═══════════════════════════════════════════════════════════
    // TEST 10 : Motif null
    // ═══════════════════════════════════════════════════════════
    public function testMotifNull(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le motif est obligatoire');

        $this->validator->validateMotif(null);
    }

    // ═══════════════════════════════════════════════════════════
    // TEST 11 : Motif valide
    // ═══════════════════════════════════════════════════════════
    public function testMotifValide(): void
    {
        $result = $this->validator->validateMotif('Rendez-vous médical');
        $this->assertTrue($result);
    }

    // ═══════════════════════════════════════════════════════════
    // TEST 12 : Utilisateur non authentifié
    // ═══════════════════════════════════════════════════════════
    public function testUtilisateurNonAuthentifie(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Utilisateur non authentifié');

        $this->validator->validateUtilisateur(null);
    }

    // ═══════════════════════════════════════════════════════════
    // TEST 13 : Utilisateur valide
    // ═══════════════════════════════════════════════════════════
    public function testUtilisateurValide(): void
    {
        $user = new Utilisateur();
        $user->setEmail('test@example.com');

        $result = $this->validator->validateUtilisateur($user);
        $this->assertTrue($result);
    }
}