<?php

namespace App\Controller\RECRUTEMENT;

use App\Entity\Candidature_externe;
use App\Entity\Candidature_interne;
use App\Entity\Poste_externe;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/analyse-rh', name: 'app_analyse_rh')]
class AnalyseRHController extends AbstractController
{
    #[Route('', name: '_index', methods: ['GET'])]
    public function index(EntityManagerInterface $em): Response
    {
        $candidaturesInternes = $em->getRepository(Candidature_interne::class)->findAll();
        $candidaturesExternes = $em->getRepository(Candidature_externe::class)->findAll();
        $postesExternes = $em->getRepository(Poste_externe::class)->findAll();

        $countInternes = count($candidaturesInternes);
        $countExternes = count($candidaturesExternes);
        $countPostesOuverts = count(array_filter(
            $postesExternes,
            static fn (Poste_externe $p) => $p->getStatut() === 'Ouvert'
        ));

        $totalCand = $countInternes + $countExternes;
        $accepted = 0;
        foreach ($candidaturesInternes as $c) {
            if (mb_stripos($c->getStatut(), 'approuv') !== false) {
                ++$accepted;
            }
        }
        foreach ($candidaturesExternes as $c) {
            if ($c->getStatut() === 'Acceptée') {
                ++$accepted;
            }
        }
        $tauxAcceptation = $totalCand > 0
            ? round(($accepted / $totalCand) * 100) . '%'
            : '0%';

        $scoreIA = 0;
        if ($countExternes > 0) {
            $sumIa = 0.0;
            foreach ($candidaturesExternes as $c) {
                $sumIa += $c->getScoringIa();
            }
            $scoreIA = (int) round($sumIa / $countExternes);
        }

        // ── Bar chart : candidatures externes par type de contrat du poste ──
        $posteById = [];
        foreach ($postesExternes as $p) {
            if ($p->getId() !== null) {
                $posteById[$p->getId()] = $p;
            }
        }
        $orderedTypes = ['CDI', 'CDD', 'STAGE', 'Mission', 'Autre'];
        $typeMap = array_fill_keys($orderedTypes, 0);
        foreach ($candidaturesExternes as $c) {
            $poste = $posteById[$c->getPosteExterneId()] ?? null;
            $tc = $poste ? $poste->getTypeContrat() : 'Autre';
            if (!isset($typeMap[$tc])) {
                $typeMap['Autre']++;
            } else {
                $typeMap[$tc]++;
            }
        }
        $barLabelDisplay = [
            'CDI' => 'CDI',
            'CDD' => 'CDD',
            'STAGE' => 'Stage',
            'Mission' => 'Mission',
            'Autre' => 'Autre',
        ];
        $barLabels = array_map(static fn (string $k) => $barLabelDisplay[$k], $orderedTypes);
        $barData = array_map(static fn (string $k) => $typeMap[$k], $orderedTypes);

        // ── Pie : Accepté / En cours / Refusé (internes + externes) ─────
        $pieBuckets = ['Accepté' => 0, 'En cours' => 0, 'Refusé' => 0];
        foreach ($candidaturesInternes as $c) {
            $this->bucketStatutInterne($c->getStatut(), $pieBuckets);
        }
        foreach ($candidaturesExternes as $c) {
            $this->bucketStatutExterne($c->getStatut(), $pieBuckets);
        }

        // ── Line chart : 6 derniers mois (clé Y-m), libellés FR courtes ──
        $now = new \DateTimeImmutable('today');
        $monthBuckets = [];
        $monthLabels = [];
        for ($i = 5; $i >= 0; --$i) {
            $d = $now->modify(sprintf('-%d months', $i))->setTime(0, 0);
            $ym = $d->format('Y-m');
            $monthBuckets[$ym] = 0;
            $monthLabels[$ym] = $this->formatMonthFr($d);
        }
        foreach ($candidaturesInternes as $c) {
            $date = $c->getDateDemande();
            if ($date !== null) {
                $ym = $date->format('Y-m');
                if (isset($monthBuckets[$ym])) {
                    $monthBuckets[$ym]++;
                }
            }
        }
        foreach ($candidaturesExternes as $c) {
            $ym = $c->getDateDepot()->format('Y-m');
            if (isset($monthBuckets[$ym])) {
                $monthBuckets[$ym]++;
            }
        }

        return $this->render('RECRUTEMENT/analyse_rh/index.html.twig', [
            'kpi_internes' => $countInternes,
            'kpi_externes' => $countExternes,
            'kpi_taux' => $tauxAcceptation,
            'kpi_score' => $scoreIA,
            'kpi_postes' => $countPostesOuverts,
            'bar_labels' => json_encode($barLabels, \JSON_THROW_ON_ERROR),
            'bar_data' => json_encode($barData, \JSON_THROW_ON_ERROR),
            'pie_labels' => json_encode(array_keys($pieBuckets), \JSON_THROW_ON_ERROR),
            'pie_data' => json_encode(array_values($pieBuckets), \JSON_THROW_ON_ERROR),
            'line_labels' => json_encode(array_values($monthLabels), \JSON_THROW_ON_ERROR),
            'line_data' => json_encode(array_values($monthBuckets), \JSON_THROW_ON_ERROR),
        ]);
    }

    /**
     * @param array<string, int> $buckets
     */
    private function bucketStatutInterne(string $statut, array &$buckets): void
    {
        $s = mb_strtolower($statut);
        if (str_contains($s, 'approuv')) {
            $buckets['Accepté']++;
        } elseif (str_contains($s, 'refus')) {
            $buckets['Refusé']++;
        } else {
            $buckets['En cours']++;
        }
    }

    /**
     * @param array<string, int> $buckets
     */
    private function bucketStatutExterne(string $statut, array &$buckets): void
    {
        match ($statut) {
            'Acceptée' => $buckets['Accepté']++,
            'Refusée' => $buckets['Refusé']++,
            default => $buckets['En cours']++,
        };
    }

    private function formatMonthFr(\DateTimeInterface $d): string
    {
        $abbr = ['', 'Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'];
        $n = (int) $d->format('n');

        return $abbr[$n] . ' ' . $d->format('Y');
    }
}
