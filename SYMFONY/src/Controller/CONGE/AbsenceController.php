<?php

namespace App\Controller\CONGE;

use App\Entity\Absence;
use App\Form\CONGE\AbsenceType;
use App\Entity\Type_absence;
use App\Entity\Utilisateur;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Service\ExcelImportService;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[Route('/absence')]
class AbsenceController extends AbstractController
{
    /** Max rows loaded on the absence index (admin / guest / manager views). */
    private const ABSENCE_INDEX_MAX_RESULTS = 500;

    /** Max rows for an employee viewing only their own absences (has WHERE + LIMIT). */
    private const ABSENCE_INDEX_USER_MAX_RESULTS = 500;

    public function __construct(
        private readonly EmailService $emailService,
    ) {}

    #[Route('/test-email', name: 'app_absence_test_email', methods: ['GET'])]
    public function testEmail(): Response
    {
        try {
            $result = $this->emailService->envoyerNotificationAbsence([
                'destinataire_email' => $_ENV['EMAIL_RH_NOTIFICATIONS'] ?? 'inssafino08@gmail.com',
                'employe_nom' => 'Test Utilisateur',
                'date' => date('d/m/Y'),
                'heure_debut' => '09:00',
                'heure_fin' => '10:30',
                'duree' => '1h30',
                'type' => 'Absence justifiée',
                'motif' => 'Test email Brevo',
                'statut' => 'En attente',
            ]);

            if ($result) {
                return new Response(
                    '<h2 style="color:green">✅ Email envoyé avec succès !</h2>'
                        . '<p>Vérifiez la boîte mail : ' . ($_ENV['EMAIL_RH_NOTIFICATIONS'] ?? 'inssafino08@gmail.com') . '</p>'
                        . '<p>Vérifiez aussi le dossier <strong>Spam</strong>.</p>'
                );
            }

            return new Response(
                '<h2 style="color:red">❌ Échec envoi email</h2>'
                    . '<p>Vérifiez le fichier <strong>var/log/dev.log</strong> pour plus de détails.</p>'
            );
        } catch (\Throwable $e) {
            return new Response(
                '<h2 style="color:red">❌ Exception PHP</h2>'
                    . '<p><strong>Message :</strong> ' . htmlspecialchars($e->getMessage()) . '</p>'
                    . '<p><strong>Fichier :</strong> ' . htmlspecialchars($e->getFile()) . '</p>'
                    . '<p><strong>Ligne :</strong> ' . $e->getLine() . '</p>'
            );
        }
    }

    #[Route('/', name: 'app_absence_index', methods: ['GET'])]
    public function index(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $listAllAbsences = $this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_RH');

        $qbAll = $em->createQueryBuilder()
            ->select('a')
            ->from(Absence::class, 'a')
            ->orderBy('a.id', 'DESC')
            ->setMaxResults(self::ABSENCE_INDEX_MAX_RESULTS);

        if (!$user) {
            $absences = $qbAll->getQuery()->getResult();
        } elseif ($listAllAbsences) {
            $absences = $qbAll->getQuery()->getResult();
        } elseif ($this->isGranted('ROLE_MANAGER') && $user instanceof Utilisateur) {
            $userDept = $user->getDepartement();
            if ($userDept === null || trim((string) $userDept) === '') {
                $absences = $em->createQueryBuilder()
                    ->select('a')
                    ->from(Absence::class, 'a')
                    ->where('a.utilisateur_id = :uid')
                    ->setParameter('uid', $user->getId())
                    ->orderBy('a.id', 'DESC')
                    ->setMaxResults(self::ABSENCE_INDEX_MAX_RESULTS)
                    ->getQuery()
                    ->getResult();
            } else {
                $absences = $em->createQueryBuilder()
                    ->select('a')
                    ->from(Absence::class, 'a')
                    ->join(Utilisateur::class, 'u', 'WITH', 'a.utilisateur_id = u.id')
                    ->where('u.departement = :dept')
                    ->setParameter('dept', $userDept)
                    ->orderBy('a.id', 'DESC')
                    ->setMaxResults(self::ABSENCE_INDEX_MAX_RESULTS)
                    ->getQuery()
                    ->getResult();
            }
        } elseif ($user instanceof Utilisateur) {
            $absences = $em->createQueryBuilder()
                ->select('a')
                ->from(Absence::class, 'a')
                ->where('a.utilisateur_id = :uid')
                ->setParameter('uid', $user->getId())
                ->orderBy('a.id', 'DESC')
                ->setMaxResults(self::ABSENCE_INDEX_USER_MAX_RESULTS)
                ->getQuery()
                ->getResult();
        } else {
            $absences = [];
        }

        $canChooseStatut = $listAllAbsences || $this->isGranted('ROLE_MANAGER');
        $formNew = $this->createForm(AbsenceType::class, new Absence(), [
            'action' => $this->generateUrl('app_absence_new'),
            'method' => 'POST',
            'can_choose_statut' => $canChooseStatut,
        ]);

        return $this->render('CONGE/absences/index.html.twig', [
            'absences'     => $absences,
            'formNew'      => $formNew->createView(),
            'openForm'     => false,
            'typesAbsence' => $em->getRepository(Type_absence::class)->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_absence_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        $post = $request->request->all();
        $raw = [];
        foreach ($post as $k => $v) {
            if (is_array($v)) {
                $raw = $v;
                break;
            }
        }

        $absence = new Absence();

        try {
            $dd = $raw['dateDebut'] ?? null;
            if ($dd) {
                $absence->setDateDebut(new \DateTime($dd));
            }
        } catch (\Exception $e) {
        }

        $df = $raw['dateFin'] ?? null;
        if ($df) {
            try {
                $absence->setDateFin(new \DateTime($df));
            } catch (\Exception $e) {
            }
        }

        $dateDebut = $absence->getDateDebut();
        $dateFin = $absence->getDateFin();

        if ($dateDebut && $dateFin === null) {
            $absence->setDateFin(clone $dateDebut);
        }

        // STATUT : seuls ADMIN / MANAGER peuvent imposer un statut ; employé → toujours « En attente »
        $canChooseStatut = $this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_RH') || $this->isGranted('ROLE_MANAGER');
        if ($canChooseStatut) {
            $absence->setStatut($raw['statut'] ?? 'En attente');
        } else {
            $absence->setStatut('En attente');
        }

        $typeId = null;
        $form = $this->createForm(AbsenceType::class, new Absence(), [
            'can_choose_statut' => $canChooseStatut,
        ]);
        $form->handleRequest($request);
        try {
            $typeEntity = $form->get('typeAbsenceId')->getData();
            if ($typeEntity !== null) {
                $typeId = $typeEntity->getId();
            }
        } catch (\Exception $e) {
        }

        if (!$typeId) {
            foreach ($raw as $k => $v) {
                if (stripos($k, 'typeAbsence') !== false || stripos($k, 'type_absence') !== false) {
                    $typeId = (int) $v;
                    break;
                }
            }
        }

        if (!$typeId) {
            $typeRepo = $em->getRepository(Type_absence::class);
            foreach ($raw as $k => $v) {
                if (is_numeric($v) && (int) $v > 0) {
                    $found = $typeRepo->find((int) $v);
                    if ($found) {
                        $typeId = (int) $v;
                        break;
                    }
                }
            }
        }
        $absence->setTypeAbsenceId($typeId ?? 1);

        $hd = trim($raw['heureDebut'] ?? '');
        if (empty($hd)) {
            $hdH = $raw['heureDebutH'] ?? '';
            $hdM = $raw['heureDebutM'] ?? '';
            $hd = ($hdH !== '' && $hdM !== '') ? $hdH . ':' . $hdM : '08:00';
        }
        $absence->setHeureDebut(substr($hd, 0, 5));

        $hf = trim($raw['heureFin'] ?? '');
        if (empty($hf)) {
            $hfH = $raw['heureFinH'] ?? '';
            $hfM = $raw['heureFinM'] ?? '';
            $hf = ($hfH !== '' && $hfM !== '') ? $hfH . ':' . $hfM : '09:00';
        }
        $absence->setHeureFin(substr($hf, 0, 5));

        $duree = (int) ($raw['dureeMinutes'] ?? 0);
        if ($duree <= 0) {
            $parts1 = explode(':', $absence->getHeureDebut());
            $parts2 = explode(':', $absence->getHeureFin());
            if (count($parts1) === 2 && count($parts2) === 2) {
                $min1 = (int) $parts1[0] * 60 + (int) $parts1[1];
                $min2 = (int) $parts2[0] * 60 + (int) $parts2[1];
                $duree = max(0, $min2 - $min1);
            }
        }
        $absence->setDureeMinutes($duree > 0 ? $duree : 60);
        $absence->setNbrJours($duree > 0 ? $duree : 1);

        if (!$user instanceof Utilisateur) {
            $this->addFlash('error', '❌ Utilisateur non authentifié.');
            return $this->redirectToRoute('app_absence_index');
        }
        $absence->setUtilisateurId($user->getId());

        $absence->setMotif($raw['motif'] ?? null);

        if (!$absence->getDateDebut()) {
            $this->addFlash('error', '❌ La date de début est obligatoire.');
            return $this->redirectToRoute('app_absence_index');
        }

        // Variables pour les notifications
        $nomEmploye = $user->getPrenom() . ' ' . $user->getNom();
        $typeLabel = 'Absence';

        $typeAbsenceEntity = $em->getRepository(Type_absence::class)->find($typeId ?? 1);
        if ($typeAbsenceEntity) {
            $typeLabel = $typeAbsenceEntity->getLibelle() ?? 'Absence';
        }

        $h = floor($duree / 60);
        $m = $duree % 60;
        $dureeFormatee = $h > 0 ? ($m > 0 ? "{$h}h{$m}" : "{$h}h") : "{$m} min";
        $emailRH = $_ENV['EMAIL_RH_NOTIFICATIONS'] ?? 'inssafino08@gmail.com';

        // ENVOI EMAIL AU RH
        $this->emailService->envoyerNotificationAbsence([
            'destinataire_email' => $emailRH,
            'employe_nom' => $nomEmploye,
            'date' => $absence->getDateDebut()->format('d/m/Y'),
            'heure_debut' => $absence->getHeureDebut(),
            'heure_fin' => $absence->getHeureFin(),
            'duree' => $dureeFormatee,
            'type' => $typeLabel,
            'motif' => $absence->getMotif() ?? 'Non précisé',
            'statut' => $absence->getStatut(),
        ]);

        // Sauvegarde avec gestion manuelle de l'ID
        $conn = $em->getConnection();
        $nextId = (int) $conn->fetchOne("SELECT COALESCE(MAX(id), 0) + 1 FROM absence");
        try {
            // Les dates sont déjà validées plus haut dans le code
            // On peut les utiliser directement
            $conn->executeStatement(
                "INSERT INTO absence (
            id, date_debut, date_fin, nbr_jours, statut,
            type_absence_id, utilisateur_id, heure_debut, heure_fin,
            duree_minutes, motif
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $nextId,
                    $absence->getDateDebut()->format('Y-m-d'),
                    $absence->getDateFin()?->format('Y-m-d'),
                    $absence->getNbrJours() ?? 1,
                    $absence->getStatut() ?? 'En attente',
                    $absence->getTypeAbsenceId() ?? 1,
                    $absence->getUtilisateurId(),
                    $absence->getHeureDebut() ?? '08:00',
                    $absence->getHeureFin() ?? '09:00',
                    $absence->getDureeMinutes() ?? 60,
                    $absence->getMotif() ?? '',
                ]
            );
            // Définir l'ID dans l'objet (nécessaire pour les liens)
            $reflection = new \ReflectionClass($absence);
            $property = $reflection->getProperty('id');
            $property->setAccessible(true);
            $property->setValue($absence, $nextId);

            $this->addFlash('success', '✅ Demande d\'absence créée avec succès !');

            // ENVOI NOTIFICATION AU MANAGER
            $manager = $this->getManagerByEmployeId($em, $user->getId());

            if ($manager && $manager->getEmail()) {
                $lienApprouver = $this->generateUrl('conges_approuver', ['id' => $nextId], UrlGeneratorInterface::ABSOLUTE_URL);
                $lienRefuser = $this->generateUrl('conges_refuser', ['id' => $nextId], UrlGeneratorInterface::ABSOLUTE_URL);

                $this->emailService->envoyerNotificationManager([
                    'manager_email' => $manager->getEmail(),
                    'employe_nom' => $nomEmploye,
                    'date' => $absence->getDateDebut()->format('d/m/Y'),
                    'type' => $typeLabel,
                    'motif' => $absence->getMotif() ?? 'Non précisé',
                    'lien_approuver' => $lienApprouver,
                    'lien_refuser' => $lienRefuser,
                ]);

                $this->addFlash('info', '📧 Le manager a été notifié de votre demande.');
            }
        } catch (\Exception $e) {
            $this->addFlash('error', '❌ Erreur BDD : ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_absence_index');
    }

    #[Route('/{id}', name: 'app_absence_show', methods: ['GET'])]
    public function show(Absence $absence, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if ($user instanceof Utilisateur && !$this->canAccessAbsence($user, $absence, $em)) {
            $this->addFlash('error', '❌ Vous ne pouvez pas voir cette demande.');
            return $this->redirectToRoute('app_absence_index');
        }

        return $this->render('CONGE/absences/show.html.twig', ['absence' => $absence]);
    }

    #[Route('/{id}/edit', name: 'app_absence_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Absence $absence, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if ($user instanceof Utilisateur && !$this->canAccessAbsence($user, $absence, $em)) {
            $this->addFlash('error', '❌ Vous ne pouvez pas modifier cette demande.');
            return $this->redirectToRoute('app_absence_index');
        }

        $canChooseStatut = $this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_RH') || $this->isGranted('ROLE_MANAGER');
        $statutOriginal = $absence->getStatut();
        $form = $this->createForm(AbsenceType::class, $absence, [
            'can_choose_statut' => $canChooseStatut,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $post = $request->request->all();
            $raw = [];
            foreach ($post as $k => $v) {
                if (is_array($v)) {
                    $raw = $v;
                    break;
                }
            }

            try {
                $typeEntity = $form->get('typeAbsenceId')->getData();
                if ($typeEntity !== null) {
                    $absence->setTypeAbsenceId($typeEntity->getId());
                }
            } catch (\Exception $e) {
            }

            $hd = trim($raw['heureDebut'] ?? $absence->getHeureDebut() ?? '');
            if (empty($hd)) {
                $hdH = $raw['heureDebutH'] ?? '08';
                $hdM = $raw['heureDebutM'] ?? '00';
                $hd = $hdH . ':' . $hdM;
            }
            $absence->setHeureDebut(substr($hd, 0, 5));

            $hf = trim($raw['heureFin'] ?? $absence->getHeureFin() ?? '');
            if (empty($hf)) {
                $hfH = $raw['heureFinH'] ?? '09';
                $hfM = $raw['heureFinM'] ?? '00';
                $hf = $hfH . ':' . $hfM;
            }
            $absence->setHeureFin(substr($hf, 0, 5));

            $duree = (int) ($raw['dureeMinutes'] ?? 0);
            if ($duree <= 0) {
                $p1 = explode(':', $absence->getHeureDebut());
                $p2 = explode(':', $absence->getHeureFin());
                if (count($p1) === 2 && count($p2) === 2) {
                    $duree = max(0, ((int) $p2[0] * 60 + (int) $p2[1]) - ((int) $p1[0] * 60 + (int) $p1[1]));
                }
            }
            $absence->setDureeMinutes($duree > 0 ? $duree : ($absence->getDureeMinutes() ?? 60));
            $absence->setNbrJours($duree > 0 ? $duree : ($absence->getNbrJours() ?? 1));

            if ($absence->getDateDebut() && !$absence->getDateFin()) {
                $absence->setDateFin(clone $absence->getDateDebut());
            }

            if (!$canChooseStatut) {
                $absence->setStatut($statutOriginal);
            }

            try {
                $em->flush();
                $this->addFlash('success', '✅ Absence modifiée avec succès !');
                return $this->redirectToRoute('app_absence_index');
            } catch (\Exception $e) {
                $this->addFlash('error', '❌ Erreur : ' . $e->getMessage());
            }
        }

        return $this->render('CONGE/absences/edit.html.twig', [
            'absence' => $absence,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/archive', name: 'app_absence_archive', methods: ['POST'])]
    public function archive(Request $request, Absence $absence, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if ($user instanceof Utilisateur && !$this->canAccessAbsence($user, $absence, $em)) {
            $this->addFlash('error', '❌ Action non autorisée.');
            return $this->redirectToRoute('app_absence_index');
        }

        if ($this->isCsrfTokenValid('archive' . $absence->getId(), $request->request->get('_token'))) {
            $absence->setStatut('Archivé');
            $em->flush();
            $this->addFlash('success', '📦 Absence archivée avec succès.');
        }
        return $this->redirectToRoute('app_absence_index');
    }

    #[Route('/{id}', name: 'app_absence_delete', methods: ['POST'])]
    public function delete(Request $request, Absence $absence, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if ($user && !$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_RH')) {
            $this->addFlash('error', '❌ Action non autorisée.');
            return $this->redirectToRoute('app_absence_index');
        }

        if ($this->isCsrfTokenValid('delete' . $absence->getId(), $request->getPayload()->getString('_token'))) {
            $em->remove($absence);
            $em->flush();
            $this->addFlash('success', '🗑️ Absence supprimée avec succès.');
        }
        return $this->redirectToRoute('app_absence_index');
    }

    #[Route('/import-excel', name: 'app_absence_import_excel', methods: ['GET', 'POST'])]
    public function importExcel(Request $request, ExcelImportService $importService): Response
    {
        if ($request->isMethod('POST')) {
            $file = $request->files->get('excel_file');

            if (!$file) {
                $this->addFlash('error', 'Veuillez sélectionner un fichier Excel');
                return $this->redirectToRoute('app_absence_import_excel');
            }

            $extension = $file->getClientOriginalExtension();
            if (!in_array($extension, ['xlsx', 'xls'])) {
                $this->addFlash('error', 'Format non supporté. Utilisez .xlsx ou .xls');
                return $this->redirectToRoute('app_absence_import_excel');
            }

            $skipFirstRow = $request->request->has('skip_first_row');
            $sendNotifications = $request->request->has('send_notifications');

            try {
                $tempFile = tempnam(sys_get_temp_dir(), 'excel_');
                $file->move(sys_get_temp_dir(), basename($tempFile));
                $result = $importService->import($tempFile, $skipFirstRow, $sendNotifications);

                if (file_exists($tempFile)) {
                    unlink($tempFile);
                }

                $this->addFlash('success', sprintf('✅ Import terminé : %d succès, %d erreurs sur %d lignes', $result['success'], $result['errors'], $result['total']));

                foreach ($result['details'] as $detail) {
                    if ($detail['status'] === 'error') {
                        $this->addFlash('warning', "Ligne {$detail['row']} : {$detail['message']}");
                    }
                }

                if ($result['success'] > 0) {
                    $this->addFlash('info', "📊 {$result['success']} absence(s) importée(s) avec succès");
                }
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de l\'import: ' . $e->getMessage());
            }

            return $this->redirectToRoute('app_absence_index');
        }

        return $this->render('absence/import_excel.html.twig');
    }

    #[Route('/download-template', name: 'app_absence_download_template', methods: ['GET'])]
    public function downloadTemplate(): Response
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'Employé ID');
        $sheet->setCellValue('B1', 'Date Début');
        $sheet->setCellValue('C1', 'Date Fin');
        $sheet->setCellValue('D1', 'Heure Début');
        $sheet->setCellValue('E1', 'Heure Fin');
        $sheet->setCellValue('F1', 'Type ID');
        $sheet->setCellValue('G1', 'Motif');

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
        ];
        $sheet->getStyle('A1:G1')->applyFromArray($headerStyle);

        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setWidth(15);
        }

        $sheet->setCellValue('A2', 1);
        $sheet->setCellValue('B2', '2025-03-10');
        $sheet->setCellValue('C2', '2025-03-10');
        $sheet->setCellValue('D2', '09:00');
        $sheet->setCellValue('E2', '12:00');
        $sheet->setCellValue('F2', 1);
        $sheet->setCellValue('G2', 'Formation');

        $sheet->setCellValue('A3', 2);
        $sheet->setCellValue('B3', '2025-03-11');
        $sheet->setCellValue('C3', '2025-03-11');
        $sheet->setCellValue('D3', '14:00');
        $sheet->setCellValue('E3', '17:00');
        $sheet->setCellValue('F3', 2);
        $sheet->setCellValue('G3', 'Réunion client');

        $tempFile = tempnam(sys_get_temp_dir(), 'template_');
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($tempFile);

        return $this->file($tempFile, 'template_import_absences.xlsx', \Symfony\Component\HttpFoundation\ResponseHeaderBag::DISPOSITION_INLINE);
    }

    private function getManagerByEmployeId(EntityManagerInterface $em, int $employeId): ?Utilisateur
    {
        $employe = $em->getRepository(Utilisateur::class)->find($employeId);

        if (!$employe) {
            return null;
        }

        $conn = $em->getConnection();

        $managerId = $conn->fetchOne(
            "SELECT u.id FROM utilisateur u 
             WHERE u.departement = :dept 
             AND (u.role = 'manager' OR u.role = 'ROLE_MANAGER')
             LIMIT 1",
            ['dept' => $employe->getDepartement()]
        );

        if ($managerId) {
            return $em->getRepository(Utilisateur::class)->find((int) $managerId);
        }

        $managerId = $conn->fetchOne(
            "SELECT u.id FROM utilisateur u 
             WHERE u.role = 'manager' OR u.role = 'ROLE_MANAGER'
             LIMIT 1",
            []
        );

        return $managerId ? $em->getRepository(Utilisateur::class)->find((int) $managerId) : null;
    }

    private function canAccessAbsence(Utilisateur $user, Absence $absence, EntityManagerInterface $em): bool
    {
        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_RH')) {
            return true;
        }
        $ownerId = $absence->getUtilisateurId();
        if ($ownerId !== null && (int) $ownerId === (int) $user->getId()) {
            return true;
        }
        if ($this->isGranted('ROLE_MANAGER')) {
            $owner = $em->getRepository(Utilisateur::class)->find($ownerId);
            if (!$owner) {
                return false;
            }
            $ud = $user->getDepartement();
            $od = $owner->getDepartement();

            return $ud !== null && $od !== null && $ud === $od;
        }

        return false;
    }
}
