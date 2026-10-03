<?php
namespace App\Service\COMPETENCE;

class CompetenceValidator
{
    /**
     * Règle métier : le niveau déclaré doit être entre 0 et 10.
     */
    public function clampNiveau(int $niveau): int
    {
        return max(0, min(10, $niveau));
    }

    /**
     * Règle métier : un gap = niveau < 60% du niveauMax.
     */
    public function isGap(int $niveauActuel, int $niveauMax): bool
    {
        if ($niveauActuel <= 0) return false;
        return $niveauActuel < round($niveauMax * 0.6);
    }

    /**
     * Règle métier : score global = moyenne (niveauActuel/niveauMax * 100).
     * @param array<array{niveauActuel: int|null, niveauMax: int}> $skills
     */
    public function computeScore(array $skills): int
    {
        $evaluated = array_filter($skills, fn($s) => $s['niveauActuel'] !== null);
        $total = count($skills);
        if ($total === 0 || count($evaluated) === 0) return 0;

        $sum = array_sum(array_map(
            fn($s) => (int)$s['niveauActuel'] / max(1, (int)$s['niveauMax']) * 100,
            $skills
        ));
        return (int) round($sum / $total);
    }

    /**
     * Règle métier : le libellé doit avoir au moins 2 caractères.
     */
    public function validateLibelle(string $libelle): bool
    {
        if (strlen(trim($libelle)) < 2) {
            throw new \InvalidArgumentException('Le libellé doit contenir au moins 2 caractères.');
        }
        return true;
    }

    /**
     * Règle métier : niveauMax doit être entre 1 et 10.
     */
    public function validateNiveauMax(int $niveauMax): int
    {
        if ($niveauMax < 1 || $niveauMax > 10) {
            throw new \InvalidArgumentException('Le niveauMax doit être entre 1 et 10.');
        }
        return $niveauMax;
    }
}