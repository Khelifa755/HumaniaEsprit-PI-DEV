<?php

namespace App\Service\CONGE;

use App\Entity\Absence;
use App\Entity\Utilisateur;

class AbsenceValidator
{
    /**
     * Règle 1 : La date de début est obligatoire
     */
    public function validateDateDebut(Absence $absence): bool
    {
        if (!$absence->getDateDebut()) {
            throw new \InvalidArgumentException('La date de début est obligatoire');
        }
        return true;
    }

    /**
     * Règle 2 : La durée doit être entre 30min et 4h (240min)
     */
    public function validateDuree(int $dureeMinutes): bool
    {
        if ($dureeMinutes < 30) {
            throw new \InvalidArgumentException('La durée minimale est de 30 minutes');
        }
        if ($dureeMinutes > 240) {
            throw new \InvalidArgumentException('La durée maximale est de 4 heures');
        }
        return true;
    }

    /**
     * Règle 3 : La date ne peut pas être dans le passé
     */
    public function validateDateNotPast(\DateTimeInterface $date): bool
    {
        $today = new \DateTime('today');
        
        if ($date < $today) {
            throw new \InvalidArgumentException('La date ne peut pas être dans le passé');
        }
        return true;
    }

    /**
     * Règle 4 : Le motif est obligatoire
     */
    public function validateMotif(?string $motif): bool
    {
        if (empty($motif)) {
            throw new \InvalidArgumentException('Le motif est obligatoire');
        }
        return true;
    }

    /**
     * Règle 5 : L'utilisateur doit être authentifié
     */
    public function validateUtilisateur(?Utilisateur $user): bool
    {
        if (!$user) {
            throw new \InvalidArgumentException('Utilisateur non authentifié');
        }
        return true;
    }

    /**
     * Validation complète d'une absence
     */
    public function validate(Absence $absence, ?Utilisateur $user): bool
    {
        $this->validateUtilisateur($user);
        $this->validateDateDebut($absence);
        $this->validateDateNotPast($absence->getDateDebut());
        $this->validateMotif($absence->getMotif());
        
        if ($absence->getDureeMinutes()) {
            $this->validateDuree($absence->getDureeMinutes());
        }
        
        return true;
    }
}