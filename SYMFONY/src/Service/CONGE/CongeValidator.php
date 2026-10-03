<?php
// src/Service/CONGE/CongeValidator.php

namespace App\Service\CONGE;

use App\Entity\Conge;

class CongeValidator
{
    /**
     * Valide les règles métier d'un congé
     * 
     * @throws \InvalidArgumentException
     */
    public function validate(Conge $conge, ?\DateTime $today = null): bool
    {
        $today = $today ?? new \DateTime('today');
        
        // 1. Vérifier que la date de début n'est pas dans le passé
        if ($conge->getDateDebut() < $today) {
            throw new \InvalidArgumentException('La date de début ne peut pas être antérieure à aujourd\'hui.');
        }
        
        // 2. Vérifier que la date de fin est après la date de début
        if ($conge->getDateFin() < $conge->getDateDebut()) {
            throw new \InvalidArgumentException('La date de fin doit être postérieure à la date de début.');
        }
        
        // 3. Vérifier que le nombre de jours est positif
        if ($conge->getNbrJours() <= 0) {
            throw new \InvalidArgumentException('Le nombre de jours doit être supérieur à 0.');
        }
        
        // 4. Vérifier que le nombre de jours ne dépasse pas 30
        if ($conge->getNbrJours() > 30) {
            throw new \InvalidArgumentException('Le nombre de jours ne peut pas dépasser 30.');
        }
        
        // 5. Vérifier que le statut n'est pas vide
        if (empty($conge->getStatut())) {
            throw new \InvalidArgumentException('Le statut est obligatoire.');
        }
        
        // 6. Vérifier que le type de congé est valide
        if (empty($conge->getTypeCongeId()) || $conge->getTypeCongeId() <= 0) {
            throw new \InvalidArgumentException('Le type de congé est obligatoire.');
        }
        
        // 7. Vérifier que l'utilisateur est valide
        if (empty($conge->getUtilisateurId()) || $conge->getUtilisateurId() <= 0) {
            throw new \InvalidArgumentException('L\'utilisateur est obligatoire.');
        }
        
        return true;
    }
    
    /**
     * Calcule le nombre de jours entre deux dates
     */
    public function calculerNbrJours(Conge $conge): int
    {
        if ($conge->getDateDebut() && $conge->getDateFin()) {
            $diff = $conge->getDateDebut()->diff($conge->getDateFin());
            return max(1, $diff->days + 1);
        }
        return 0;
    }
}