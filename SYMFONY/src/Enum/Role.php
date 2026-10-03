<?php

namespace App\Enum;

enum Role: string
{
    case ADMIN     = 'ADMIN';
    case RH        = 'RH';
    case MANAGER   = 'MANAGER';
    case FORMATEUR = 'FORMATEUR';
    case EMPLOYE   = 'EMPLOYE';
    case CANDIDAT  = 'CANDIDAT';

    public function label(): string
    {
        return match($this) {
            Role::ADMIN     => 'Administrateur',
            Role::RH        => 'Ressources Humaines',
            Role::MANAGER   => 'Manager',
            Role::FORMATEUR => 'Formateur',
            Role::EMPLOYE   => 'Employé',
            Role::CANDIDAT  => 'Candidat',
        };
    }

    public function color(): string
    {
        return match($this) {
            Role::ADMIN     => '#7c3aed',
            Role::RH        => '#2563eb',
            Role::MANAGER   => '#0891b2',
            Role::FORMATEUR => '#059669',
            Role::EMPLOYE   => '#64748b',
            Role::CANDIDAT  => '#f59e0b',
        };
    }
}