<?php

namespace App\Controller\PLANNIFICATION;

use App\Entity\Offboarding;
use App\Entity\OffboardingTask;
use App\Entity\Publication;
use App\Entity\Utilisateur;
use App\Repository\Utilisateur\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/offboarding', name: 'app_offboarding_')]
final class OffboardingController extends AbstractController
{
    // ── Reasons ───────────────────────────────────────────────────────────
    private const REASONS = [
        'Démission',
        'Fin de contrat',
        'Licenciement',
        'Retraite',
        'Rupture conventionnelle',
        'Mutation',
    ];

    private const COMMON_TASKS = [
        ['icon' => '📋', 'label' => 'Lettre de démission / notification officielle reçue', 'category' => 'Administratif', 'dayOffset' => 30],
        ['icon' => '🗓',  'label' => 'Entretien de départ planifié',                        'category' => 'RH',            'dayOffset' => 14],
        ['icon' => '📝', 'label' => 'Entretien de départ effectué',                         'category' => 'RH',            'dayOffset' => 7],
        ['icon' => '🔄', 'label' => 'Plan de transfert de connaissances',                   'category' => 'Opérationnel',  'dayOffset' => 14],
        ['icon' => '👥', 'label' => 'Transmission des responsabilités au remplaçant',       'category' => 'Opérationnel',  'dayOffset' => 5],
        ['icon' => '📂', 'label' => 'Archivage et transfert des fichiers de travail',       'category' => 'IT',            'dayOffset' => 3],
        ['icon' => '🖥',  'label' => 'Restitution du matériel informatique',                'category' => 'Équipement',    'dayOffset' => 0],
        ['icon' => '🪪', 'label' => 'Restitution du badge d\'accès',                        'category' => 'Accès',         'dayOffset' => 0],
        ['icon' => '🔐', 'label' => 'Révocation de tous les accès informatiques',           'category' => 'IT',            'dayOffset' => 0],
        ['icon' => '📧', 'label' => 'Désactivation du compte email',                        'category' => 'IT',            'dayOffset' => 0],
        ['icon' => '🏥', 'label' => 'Clôture de l\'affiliation mutuelle',                   'category' => 'Administratif', 'dayOffset' => 0],
        ['icon' => '💰', 'label' => 'Solde de tout compte préparé',                         'category' => 'Finance',       'dayOffset' => 0],
        ['icon' => '📄', 'label' => 'Certificat de travail rédigé',                         'category' => 'RH',            'dayOffset' => 0],
        ['icon' => '⭐', 'label' => 'Lettre de recommandation (si demandée)',                'category' => 'RH',            'dayOffset' => 0],
    ];

    private const REASON_TASKS = [
        'Retraite' => [
            ['icon' => '🎂', 'label' => 'Organisation de l\'événement de départ', 'category' => 'Intégration', 'dayOffset' => 7],
            ['icon' => '🏆', 'label' => 'Cérémonie de remise de cadeau',           'category' => 'Intégration', 'dayOffset' => 0],
            ['icon' => '📸', 'label' => 'Photo souvenir avec l\'équipe',            'category' => 'Intégration', 'dayOffset' => 0],
        ],
        'Mutation' => [
            ['icon' => '🔀', 'label' => 'Coordination avec le site d\'accueil',       'category' => 'Opérationnel', 'dayOffset' => 14],
            ['icon' => '📦', 'label' => 'Transfert du dossier RH au nouveau site',    'category' => 'RH',           'dayOffset' => 5],
            ['icon' => '🪪', 'label' => 'Création badge nouveau site',                'category' => 'Accès',        'dayOffset' => 0],
        ],
        'Licenciement' => [
            ['icon' => '⚖️', 'label' => 'Respect du délai de préavis légal',          'category' => 'Administratif', 'dayOffset' => 30],
            ['icon' => '📜', 'label' => 'Remise des documents légaux obligatoires',    'category' => 'Administratif', 'dayOffset' => 0],
            ['icon' => '💼', 'label' => 'Contact avec l\'accompagnement emploi',       'category' => 'RH',            'dayOffset' => 0],
        ],
    ];

    // ─────────────────────────────────────────────────────────────────────

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(
        UtilisateurRepository $utilisateurRepo,
        EntityManagerInterface $em
    ): Response {
        $utilisateurs = $utilisateurRepo->findAll();

        // Check if the current user already has an active offboarding session
        $existingOffboarding = null;
        $existingTasksData   = [];

        $currentUser = $this->getUser();
        if ($currentUser instanceof Utilisateur) {
            $existingOffboarding = $em->getRepository(Offboarding::class)->findOneBy(
                ['utilisateur' => $currentUser, 'status' => 'in_progress'],
                ['startedAt' => 'DESC']
            );

            if ($existingOffboarding) {
                foreach ($existingOffboarding->getTasks() as $task) {
                    $existingTasksData[] = [
                        'id'     => $task->getId(),
                        'label'  => $task->getLabel(),
                        'isDone' => $task->isDone(),
                    ];
                }
            }
        }

        return $this->render('offboarding/index.html.twig', [
            'utilisateurs'        => $utilisateurs,
            'reasons'             => self::REASONS,
            'existingOffboarding' => $existingOffboarding,
            'existingTasksData'   => $existingTasksData,
        ]);
    }

    // ── Generate ──────────────────────────────────────────────────────────
    #[Route('/generate', name: 'generate', methods: ['POST'])]
    public function generate(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data          = json_decode($request->getContent(), true);
        $utilisateurId = (int) ($data['employeeId'] ?? $data['employee'] ?? 0);
        $reason        = trim($data['reason'] ?? '');
        $date          = $data['departureDate'] ?? date('Y-m-d', strtotime('+30 days'));

        if (!$utilisateurId) {
            return $this->json(['error' => 'Veuillez sélectionner un employé.'], Response::HTTP_BAD_REQUEST);
        }
        if (empty($reason)) {
            return $this->json(['error' => 'Veuillez sélectionner le motif de départ.'], Response::HTTP_BAD_REQUEST);
        }

        /** @var Utilisateur|null $utilisateur */
        $utilisateur = $em->getRepository(Utilisateur::class)->find($utilisateurId);
        if (!$utilisateur) {
            return $this->json(['error' => 'Employé introuvable.'], Response::HTTP_NOT_FOUND);
        }

        // Reuse existing in_progress record OR create new one
        $offboarding = $em->getRepository(Offboarding::class)->findOneBy([
            'utilisateur' => $utilisateur,
            'status'      => 'in_progress',
        ]);

        if (!$offboarding) {
            $offboarding = new Offboarding();
            $offboarding->setUtilisateur($utilisateur);
            $offboarding->setStatus('in_progress');
            $offboarding->setStartedAt(new \DateTime());
        }

        $offboarding->setReason($reason);
        $offboarding->setDepartureDate(new \DateTime($date));

        // Preserve existing tasks by label
        $existingTasks = [];
        foreach ($offboarding->getTasks() as $old) {
            $existingTasks[$old->getLabel()] = $old;
        }

        $taskTemplates = $this->buildTaskTemplates($reason);
        
        foreach ($taskTemplates as $tpl) {
            $label = $tpl['label'];
            if (isset($existingTasks[$label])) {
                // Task already exists, keep it
                unset($existingTasks[$label]);
            } else {
                // New task
                $task = new OffboardingTask();
                $task->setLabel($label);
                $task->setIsDone(false);
                $offboarding->addTask($task);
                $em->persist($task);
            }
        }

        // Remove old tasks that are no longer in the templates
        foreach ($existingTasks as $old) {
            $offboarding->removeTask($old);
            $em->remove($old);
        }

        $em->persist($offboarding);
        $em->flush();

        // Build JS-friendly task list
        $tasks = [];
        
        foreach ($taskTemplates as $tpl) {
            $label = $tpl['label'];
            $matchingTask = null;
            foreach ($offboarding->getTasks() as $t) {
                if ($t->getLabel() === $label) {
                    $matchingTask = $t;
                    break;
                }
            }
            
            if ($matchingTask) {
                $tasks[] = [
                    'id'        => $matchingTask->getId(),
                    'icon'      => $tpl['icon']      ?? '📋',
                    'label'     => $matchingTask->getLabel(),
                    'category'  => $tpl['category']  ?? 'Général',
                    'dayOffset' => $tpl['dayOffset']  ?? 0,
                    'done'      => $matchingTask->isDone(),
                ];
            }
        }

        $name  = trim(($utilisateur->getPrenom() ?? '') . ' ' . ($utilisateur->getNom() ?? ''));
        $dept  = $utilisateur->getDepartement() ?? '—';
        $email = $utilisateur->getEmail() ?? '—';
        $role  = $utilisateur->getPosteActuel() ?? 'Collaborateur';

        return $this->json([
            'offboardingId' => $offboarding->getId(),
            'tasks'         => $tasks,
            'employeeInfo'  => [
                'name'          => $name,
                'department'    => $dept,
                'email'         => $email,
                'role'          => $role,
                'reason'        => $reason,
                'departureDate' => $date,
            ],
            'autoActions'   => $this->buildAutoActions($name, $reason, $email),
            'postPublished' => $offboarding->isPostPublished(),
        ]);
    }

    // ── Toggle task done/undone ───────────────────────────────────────────
    #[Route('/task/{id}/toggle', name: 'task_toggle', methods: ['POST'])]
    public function toggleTask(int $id, EntityManagerInterface $em): JsonResponse
    {
        $task = $em->getRepository(OffboardingTask::class)->find($id);
        if (!$task) {
            return $this->json(['error' => 'Tâche introuvable.'], Response::HTTP_NOT_FOUND);
        }

        $task->setIsDone(!$task->isDone());

        $offboarding = $task->getOffboarding();
        $allDone     = true;
        foreach ($offboarding->getTasks() as $t) {
            if (!$t->isDone()) { $allDone = false; break; }
        }

        if ($allDone) {
            $offboarding->setStatus('completed');
            $offboarding->setCompletedAt(new \DateTime());
            $offboarding->setCanArchive(true);
        } else {
            if ($offboarding->getStatus() === 'completed') {
                $offboarding->setStatus('in_progress');
                $offboarding->setCompletedAt(null);
                $offboarding->setCanArchive(false);
            }
        }

        $em->flush();

        return $this->json([
            'isDone'     => $task->isDone(),
            'allDone'    => $allDone,
            'offboarding' => [
                'status'     => $offboarding->getStatus(),
                'canArchive' => $offboarding->isCanArchive(),
            ],
        ]);
    }

    // ── Mark all tasks done/reset ─────────────────────────────────────────
    #[Route('/{id}/mark-all', name: 'mark_all', methods: ['POST'])]
    public function markAll(int $id, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $offboarding = $em->getRepository(Offboarding::class)->find($id);
        if (!$offboarding) {
            return $this->json(['error' => 'Offboarding introuvable.'], Response::HTTP_NOT_FOUND);
        }

        $done = (bool) (json_decode($request->getContent(), true)['done'] ?? true);

        foreach ($offboarding->getTasks() as $task) {
            $task->setIsDone($done);
        }

        if ($done) {
            $offboarding->setStatus('completed');
            $offboarding->setCompletedAt(new \DateTime());
            $offboarding->setCanArchive(true);
        } else {
            $offboarding->setStatus('in_progress');
            $offboarding->setCompletedAt(null);
            $offboarding->setCanArchive(false);
        }

        $em->flush();

        return $this->json(['success' => true, 'done' => $done]);
    }

    // ── Publish post — professional announcement ──────────────────────────
    #[Route('/{id}/publish-post', name: 'publish_post', methods: ['POST'])]
    public function publishPost(int $id, EntityManagerInterface $em): JsonResponse
    {
        $offboarding = $em->getRepository(Offboarding::class)->find($id);
        if (!$offboarding) {
            return $this->json(['error' => 'Offboarding introuvable.'], Response::HTTP_NOT_FOUND);
        }

        if ($offboarding->isPostPublished()) {
            return $this->json(['error' => 'Un post a déjà été publié pour cet offboarding.'], Response::HTTP_CONFLICT);
        }

        $utilisateur = $offboarding->getUtilisateur();
        $prenom      = $utilisateur->getPrenom() ?? '';
        $nom         = $utilisateur->getNom() ?? '';
        $name        = trim("$prenom $nom");
        $dept        = $utilisateur->getDepartement() ?? '—';
        $role        = $utilisateur->getPosteActuel() ?? 'Collaborateur';
        $reason      = $offboarding->getReason() ?? '—';
        $departure   = $offboarding->getDepartureDate()
            ? $offboarding->getDepartureDate()->format('d/m/Y')
            : date('d/m/Y');

        // Adapt tone to the reason
        $tone = match ($reason) {
            'Retraite'            => "🎉 Après une belle carrière bien méritée, %s prend sa retraite le %s.\n\nNous lui souhaitons une retraite épanouie et pleine de bonheur ! Merci pour tout ce que tu as apporté à Humania. 🌟",
            'Mutation'            => "📦 %s (%s — %s) rejoint un autre site Humania à partir du %s.\nNous lui souhaitons plein succès dans cette nouvelle étape ! 🚀",
            'Rupture conventionnelle',
            'Fin de contrat'      => "🤝 %s (%s — %s) termine son aventure au sein de Humania le %s.\nMerci pour ta contribution et tes efforts. Bonne continuation ! 👋",
            default               => "👋 %s (%s — %s) quitte nos équipes le %s suite à une %s.\nNous lui souhaitons le meilleur pour la suite. Merci pour tout ! 🙏",
        };

        $contenu = match ($reason) {
            'Retraite' => sprintf($tone, $name, $departure),
            'Mutation' => sprintf($tone, $name, $dept, $role, $departure),
            'Rupture conventionnelle', 'Fin de contrat' => sprintf($tone, $name, $dept, $role, $departure),
            default    => sprintf($tone, $name, $dept, $role, $departure, strtolower($reason)),
        };

        $contenu .= "\n\n#Humania #Offboarding #" . str_replace(' ', '', ucwords($reason));

        $post = new Publication();
        $post->setContenu($contenu);
        $post->setDateCreation(new \DateTime());
        $post->setStatut('ACTIF');
        $post->setVisibility('PUBLIC');
        $post->setType('offboarding');

        $em->persist($post);

        $offboarding->setPostPublished(true);
        $em->flush();

        return $this->json(['success' => true, 'contenu' => $contenu]);
    }

    // ── Load saved offboarding for an employee ────────────────────────────
    #[Route('/load/{utilisateurId}', name: 'load', methods: ['GET'])]
    public function load(int $utilisateurId, EntityManagerInterface $em): JsonResponse
    {
        $utilisateur = $em->getRepository(Utilisateur::class)->find($utilisateurId);
        if (!$utilisateur) {
            return $this->json(['offboarding' => null]);
        }

        $offboarding = $em->getRepository(Offboarding::class)->findOneBy(
            ['utilisateur' => $utilisateur],
            ['startedAt'   => 'DESC']
        );

        if (!$offboarding) {
            return $this->json(['offboarding' => null]);
        }

        $tasks = [];
        foreach ($offboarding->getTasks() as $task) {
            $tasks[] = [
                'id'     => $task->getId(),
                'label'  => $task->getLabel(),
                'isDone' => $task->isDone(),
            ];
        }

        $name = trim(($utilisateur->getPrenom() ?? '') . ' ' . ($utilisateur->getNom() ?? ''));

        return $this->json([
            'offboarding' => [
                'id'            => $offboarding->getId(),
                'status'        => $offboarding->getStatus(),
                'postPublished' => $offboarding->isPostPublished(),
                'canArchive'    => $offboarding->isCanArchive(),
                'reason'        => $offboarding->getReason(),
                'departureDate' => $offboarding->getDepartureDate()?->format('Y-m-d'),
                'tasks'         => $tasks,
                'employeeInfo'  => [
                    'name'       => $name,
                    'department' => $utilisateur->getDepartement() ?? '—',
                    'email'      => $utilisateur->getEmail() ?? '—',
                    'role'       => $utilisateur->getPosteActuel() ?? 'Collaborateur',
                    'reason'     => $offboarding->getReason() ?? '—',
                ],
            ],
        ]);
    }

    // ── Private helpers ───────────────────────────────────────────────────

    private function buildTaskTemplates(string $reason): array
    {
        $tasks = self::COMMON_TASKS;
        foreach ((self::REASON_TASKS[$reason] ?? []) as $task) {
            $tasks[] = $task;
        }
        usort($tasks, fn($a, $b) => $b['dayOffset'] <=> $a['dayOffset']);
        return $tasks;
    }

    private function buildAutoActions(string $employee, string $reason, string $email): array
    {
        $login = explode('@', $email)[0];

        $actions = [
            ['icon' => '📧', 'label' => 'Email de notification envoyé au manager', 'detail' => 'Copie RH en CC',                       'color' => 'red'],
            ['icon' => '🗓',  'label' => 'Entretien de départ planifié',            'detail' => 'Calendrier mis à jour',                 'color' => 'yellow'],
            ['icon' => '🔐', 'label' => 'Alerte IT — révocation accès planifiée',   'detail' => 'Le jour de départ : ' . $login,         'color' => 'slate'],
            ['icon' => '📂', 'label' => 'Dossier de départ créé',                  'detail' => 'Archivage automatique RH',              'color' => 'purple'],
            ['icon' => '💰', 'label' => 'Service paie notifié',                    'detail' => 'Solde de tout compte à préparer',       'color' => 'green'],
            ['icon' => '📄', 'label' => 'Génération documents légaux déclenchée',  'detail' => 'Certificat de travail + attestation',   'color' => 'blue'],
        ];

        if ($reason === 'Retraite') {
            $actions[] = ['icon' => '🎉', 'label' => 'Invitation départ en retraite envoyée', 'detail' => 'À toute l\'équipe', 'color' => 'yellow'];
        }

        return $actions;
    }
}
