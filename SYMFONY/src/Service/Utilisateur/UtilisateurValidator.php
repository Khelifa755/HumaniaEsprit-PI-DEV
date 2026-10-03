<?php

namespace App\Service\Utilisateur;

use App\Entity\Utilisateur;

class UtilisateurValidator
{
    /**
     * Rule 1: Password must be at least 6 characters
     * Rule 2: Email must be valid
     * Rule 3: Nom and Prénom are required
     * Rule 4: Phone number must be numeric if provided
     */
    public function validate(Utilisateur $u): bool
    {
        if (empty(trim($u->getNom() ?? ''))) {
            throw new \InvalidArgumentException('Le nom est obligatoire.');
        }

        if (empty(trim($u->getPrenom() ?? ''))) {
            throw new \InvalidArgumentException('Le prénom est obligatoire.');
        }

        if (!filter_var($u->getEmail(), FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Email invalide.');
        }

        if ($u->getNumtel() !== null && !preg_match('/^\d{8,15}$/', $u->getNumtel())) {
            throw new \InvalidArgumentException('Numéro de téléphone invalide.');
        }

        return true;
    }

    public function validatePassword(string $password): bool
    {
        if (strlen($password) < 6) {
            throw new \InvalidArgumentException(
                'Le mot de passe doit contenir au moins 6 caractères.'
            );
        }
        return true;
    }
}