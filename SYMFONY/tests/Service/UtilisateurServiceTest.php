<?php

namespace App\Tests\Service;

use App\Entity\Utilisateur;
use App\Service\Utilisateur\UtilisateurValidator;
use PHPUnit\Framework\TestCase;

class UtilisateurServiceTest extends TestCase
{
    private UtilisateurValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new UtilisateurValidator();
    }

    // ── Rule 1: Valid user passes all checks ──────────────────────────────────

    public function testValidUtilisateur(): void
    {
        $user = new Utilisateur();
        $user->setNom('Ben Ali');
        $user->setPrenom('Sami');
        $user->setEmail('sami.benali@gmail.com');
        $user->setNumtel('22334455');

        $this->assertTrue($this->validator->validate($user));
    }

    // ── Rule 2: Nom is required ───────────────────────────────────────────────

    public function testNomObligatoire(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le nom est obligatoire.');

        $user = new Utilisateur();
        $user->setPrenom('Sami');
        $user->setEmail('sami@gmail.com');

        $this->validator->validate($user);
    }

    // ── Rule 3: Prénom is required ────────────────────────────────────────────

    public function testPrenomObligatoire(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le prénom est obligatoire.');

        $user = new Utilisateur();
        $user->setNom('Ben Ali');
        $user->setEmail('sami@gmail.com');

        $this->validator->validate($user);
    }

    // ── Rule 4: Email must be valid ───────────────────────────────────────────

    public function testEmailInvalide(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Email invalide.');

        $user = new Utilisateur();
        $user->setNom('Ben Ali');
        $user->setPrenom('Sami');
        $user->setEmail('email_pas_valide');

        $this->validator->validate($user);
    }

    // ── Rule 5: Phone must be numeric digits only ─────────────────────────────

    public function testNumtelInvalide(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Numéro de téléphone invalide.');

        $user = new Utilisateur();
        $user->setNom('Ben Ali');
        $user->setPrenom('Sami');
        $user->setEmail('sami@gmail.com');
        $user->setNumtel('abc-xyz');

        $this->validator->validate($user);
    }

    // ── Rule 6: Phone is optional — null should pass ──────────────────────────

    public function testNumtelNullAccepte(): void
    {
        $user = new Utilisateur();
        $user->setNom('Ben Ali');
        $user->setPrenom('Sami');
        $user->setEmail('sami@gmail.com');
        // no phone set

        $this->assertTrue($this->validator->validate($user));
    }

    // ── Rule 7: Password minimum 6 characters ─────────────────────────────────

    public function testPasswordTropCourt(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le mot de passe doit contenir au moins 6 caractères.');

        $this->validator->validatePassword('abc');
    }

    // ── Rule 8: Valid password passes ────────────────────────────────────────

    public function testPasswordValide(): void
    {
        $this->assertTrue($this->validator->validatePassword('motdepasse123'));
    }
}