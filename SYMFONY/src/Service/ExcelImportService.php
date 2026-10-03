<?php
// src/Service/ExcelImportService.php

namespace App\Service;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Absence;
use App\Entity\Type_absence;
use App\Entity\Utilisateur;
use Psr\Log\LoggerInterface;

class ExcelImportService
{
    private EntityManagerInterface $em;
    private EmailService $emailService;
    private LoggerInterface $logger;

    public function __construct(
        EntityManagerInterface $em,
        EmailService $emailService,
        LoggerInterface $logger
    ) {
        $this->em = $em;
        $this->emailService = $emailService;
        $this->logger = $logger;
    }

    public function import(string $filePath, bool $skipFirstRow = true, bool $sendNotifications = false): array
    {
        $result = [
            'success' => 0,
            'errors' => 0,
            'total' => 0,
            'details' => [],
        ];

        try {
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            if (empty($rows)) {
                throw new \Exception('Le fichier Excel est vide');
            }

            $startRow = $skipFirstRow ? 1 : 0;
            $result['total'] = count($rows) - $startRow;

            for ($i = $startRow; $i < count($rows); $i++) {
                $row = $rows[$i];
                $rowNumber = $i + 1;

                try {
                    $absence = $this->createAbsenceFromRow($row);
                    
                    if ($absence) {
                        $this->em->persist($absence);
                        $result['success']++;
                        $result['details'][] = [
                            'row' => $rowNumber,
                            'status' => 'success',
                            'message' => 'Absence importée avec succès'
                        ];
                        
                        if ($sendNotifications) {
                            $this->sendNotification($absence);
                        }
                    }
                } catch (\Exception $e) {
                    $result['errors']++;
                    $result['details'][] = [
                        'row' => $rowNumber,
                        'status' => 'error',
                        'message' => $e->getMessage()
                    ];
                    $this->logger->error('Erreur import ligne ' . $rowNumber . ': ' . $e->getMessage());
                }
            }

            $this->em->flush();
            
        } catch (\Exception $e) {
            $this->logger->error('Erreur import Excel: ' . $e->getMessage());
            throw $e;
        }

        return $result;
    }

    private function createAbsenceFromRow(array $row): ?Absence
    {
        // Vérifier les données minimales
        if (empty($row[0]) || empty($row[1])) {
            throw new \Exception('Employé ID et Date début sont obligatoires');
        }

        $absence = new Absence();

        // Utilisateur ID (colonne A)
        $userId = (int)$row[0];
        $user = $this->em->getRepository(Utilisateur::class)->find($userId);
        if (!$user) {
            throw new \Exception("Utilisateur ID {$userId} non trouvé");
        }
        $absence->setUtilisateurId($userId);

        // Date début (colonne B)
        $dateDebut = $this->parseDate($row[1]);
        if (!$dateDebut) {
            throw new \Exception('Date début invalide: ' . $row[1]);
        }
        $absence->setDateDebut($dateDebut);

        // Date fin (colonne C) - optionnelle
        if (!empty($row[2])) {
            $dateFin = $this->parseDate($row[2]);
            $absence->setDateFin($dateFin ?: clone $dateDebut);
        } else {
            $absence->setDateFin(clone $dateDebut);
        }

        // Heures (colonnes D et E)
        $heureDebut = !empty($row[3]) ? $this->parseTime($row[3]) : '09:00';
        $heureFin = !empty($row[4]) ? $this->parseTime($row[4]) : '17:00';
        $absence->setHeureDebut($heureDebut);
        $absence->setHeureFin($heureFin);

        // Calcul de la durée
        $duree = $this->calculateDuration($heureDebut, $heureFin);
        $absence->setDureeMinutes($duree);
        $absence->setNbrJours($duree);

        // Type d'absence (colonne F)
        $typeId = !empty($row[5]) ? (int)$row[5] : 1;
        $type = $this->em->getRepository(Type_absence::class)->find($typeId);
        if (!$type) {
            throw new \Exception("Type d'absence ID {$typeId} non trouvé");
        }
        $absence->setTypeAbsenceId($typeId);

        // Motif (colonne G) - optionnel
        $absence->setMotif(!empty($row[6]) ? $row[6] : 'Import Excel');

        // Statut par défaut
        $absence->setStatut('En attente');

        return $absence;
    }

    private function parseDate($value): ?\DateTime
    {
        if ($value instanceof \DateTime) {
            return $value;
        }
        
        if (is_numeric($value)) {
            try {
                $timestamp = Date::excelToTimestamp($value);
                return \DateTime::createFromFormat('U', $timestamp);
            } catch (\Exception $e) {
                return null;
            }
        }
        
        if (is_string($value)) {
            $formats = ['Y-m-d', 'd/m/Y', 'm/d/Y', 'd-m-Y', 'Ymd'];
            foreach ($formats as $format) {
                $date = \DateTime::createFromFormat($format, $value);
                if ($date) {
                    return $date;
                }
            }
        }
        
        return null;
    }

    private function parseTime($value): string
    {
        if ($value instanceof \DateTime) {
            return $value->format('H:i');
        }
        
        if (is_string($value) && preg_match('/^\d{1,2}:\d{2}$/', $value)) {
            return $value;
        }
        
        if (is_numeric($value)) {
            $hours = floor($value * 24);
            $minutes = round(($value * 24 - $hours) * 60);
            return sprintf('%02d:%02d', $hours, $minutes);
        }
        
        return '09:00';
    }

    private function calculateDuration(string $heureDebut, string $heureFin): int
    {
        $parts1 = explode(':', $heureDebut);
        $parts2 = explode(':', $heureFin);
        
        if (count($parts1) === 2 && count($parts2) === 2) {
            $min1 = (int)$parts1[0] * 60 + (int)$parts1[1];
            $min2 = (int)$parts2[0] * 60 + (int)$parts2[1];
            return max(30, $min2 - $min1);
        }
        
        return 60;
    }

    private function sendNotification(Absence $absence): void
    {
        try {
            $typeLabel = 'Absence';
            $type = $this->em->getRepository(Type_absence::class)->find($absence->getTypeAbsenceId());
            if ($type) {
                $typeLabel = $type->getLibelle() ?? 'Absence';
            }

            $duree = $absence->getDureeMinutes();
            $h = floor($duree / 60);
            $m = $duree % 60;
            $dureeFormatee = $h > 0 ? ($m > 0 ? "{$h}h{$m}" : "{$h}h") : "{$m} min";

            $this->emailService->envoyerNotificationAbsence([
                'destinataire_email' => $_ENV['EMAIL_RH_NOTIFICATIONS'] ?? 'admin@humania.tn',
                'employe_nom' => 'Import Excel',
                'date' => $absence->getDateDebut()->format('d/m/Y'),
                'heure_debut' => $absence->getHeureDebut(),
                'heure_fin' => $absence->getHeureFin(),
                'duree' => $dureeFormatee,
                'type' => $typeLabel,
                'motif' => $absence->getMotif() ?? 'Importation collective',
                'statut' => $absence->getStatut(),
            ]);
        } catch (\Exception $e) {
            $this->logger->warning('Notification non envoyée: ' . $e->getMessage());
        }
    }
    
}