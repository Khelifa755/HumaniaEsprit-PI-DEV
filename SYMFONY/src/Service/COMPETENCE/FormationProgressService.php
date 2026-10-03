<?php
namespace App\Service\COMPETENCE;

class FormationProgressService
{
    /**
     * Règle métier : progression = (modules complétés / total) * 100
     */
    public function computeProgression(int $doneModules, int $totalModules): int
    {
        if ($totalModules === 0) return 0;
        return (int) round($doneModules * 100 / $totalModules);
    }

    /**
     * Règle métier : statut = "Completed" si progression >= 100
     */
    public function computeStatut(int $progression): string
    {
        return $progression >= 100 ? 'Completed' : 'In Progress';
    }

    /**
     * Règle métier : certificat accordé si noteFinale >= 70
     */
    public function isCertificateEarned(?float $noteFinale): bool
    {
        return $noteFinale !== null && $noteFinale >= 70;
    }
}