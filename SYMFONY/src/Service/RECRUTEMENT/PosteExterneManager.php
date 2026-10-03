<?php

namespace App\Service\RECRUTEMENT;

use App\Entity\Poste_externe;

class PosteExterneManager
{
    /** @var string[] */
    private array $statutsValides = ['Ouvert', 'Fermé', 'En attente'];

    public function validate(Poste_externe $poste): bool
    {
        // Règle 1 : Le titre est obligatoire
        if (empty($poste->getTitre())) {
            throw new \InvalidArgumentException('Le titre du poste est obligatoire.');
        }

        // Règle 2 : Le salaire doit être positif
        if ($poste->getSalaire() <= 0) {
            throw new \InvalidArgumentException('Le salaire doit être supérieur à 0.');
        }

        // Règle 3 : La date de clôture doit être après la date de publication
        if ($poste->getDateCloture() <= $poste->getDatePublication()) {
            throw new \InvalidArgumentException('La date de clôture doit être postérieure à la date de publication.');
        }

        // Règle 4 : Le nombre d'employés doit être >= 1
        if ($poste->getNombreEmploye() < 1) {
            throw new \InvalidArgumentException('Le nombre d\'employés doit être au moins 1.');
        }

        // Règle 5 : Le statut doit être valide
        if (!in_array($poste->getStatut(), $this->statutsValides, true)) {
            throw new \InvalidArgumentException('Le statut doit être : Ouvert, Fermé ou En attente.');
        }

        return true;
    }
}