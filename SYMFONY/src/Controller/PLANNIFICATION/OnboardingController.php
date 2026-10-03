<?php

namespace App\Controller\PLANNIFICATION;

use App\Entity\Employe;
use App\Entity\Onboarding;
use App\Entity\OnboardingTask;
use App\Entity\Publication;
use App\Entity\Utilisateur;
use App\Event\EmployeeActivatedEvent;
use App\Repository\Utilisateur\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/onboarding', name: 'app_onboarding_')]
final class OnboardingController extends AbstractController
{
    public function __construct(
        private readonly EventDispatcherInterface $dispatcher,
    ) {}

    // ── Departments ───────────────────────────────────────────────────────
    private const DEPARTMENTS = [
        'Ingénierie',
        'Marketing',
        'RH',
        'Finance',
        'Ventes',
        'Support',
        'Juridique',
    ];

    private const COMMON_TASKS = [
        ['icon' => '📋', 'label' => 'Signature du contrat de travail',        'category' => 'Administratif', 'dayOffset' => 0],
        ['icon' => '🪪', 'label' => 'Création du badge d\'accès',              'category' => 'Accès',         'dayOffset' => 0],
        ['icon' => '📧', 'label' => 'Création du compte email professionnel',  'category' => 'IT',            'dayOffset' => 0],
        ['icon' => '🖥',  'label' => 'Configuration du poste de travail',      'category' => 'IT',            'dayOffset' => 1],
        ['icon' => '📚', 'label' => 'Remise du livret d\'accueil',             'category' => 'RH',            'dayOffset' => 1],
        ['icon' => '👥', 'label' => 'Présentation à l\'équipe',                'category' => 'Intégration',   'dayOffset' => 1],
        ['icon' => '🏥', 'label' => 'Affiliation mutuelle / prévoyance',       'category' => 'Administratif', 'dayOffset' => 2],
        ['icon' => '🔐', 'label' => 'Accès aux outils internes (intranet…)',   'category' => 'IT',            'dayOffset' => 2],
        ['icon' => '🧭', 'label' => 'Visite des locaux et règles de sécurité', 'category' => 'Intégration',   'dayOffset' => 3],
        ['icon' => '📝', 'label' => 'Entretien d\'intégration J+30',           'category' => 'RH',            'dayOffset' => 30],
    ];

    private const DEPT_TASKS = [
        'Ingénierie' => [
            ['icon' => '💻', 'label' => 'Accès GitHub / GitLab',              'category' => 'IT',        'dayOffset' => 1],
            ['icon' => '🐳', 'label' => 'Installation environnement dev',      'category' => 'IT',        'dayOffset' => 2],
            ['icon' => '📖', 'label' => 'Lecture de la doc technique',         'category' => 'Formation', 'dayOffset' => 3],
            ['icon' => '🔑', 'label' => 'Accès aux serveurs de staging',       'category' => 'IT',        'dayOffset' => 5],
            ['icon' => '🤝', 'label' => 'Pair-programming avec le mentor',     'category' => 'Formation', 'dayOffset' => 7],
        ],
        'Marketing' => [
            ['icon' => '🎨', 'label' => 'Accès Canva / Adobe Suite',           'category' => 'IT',          'dayOffset' => 1],
            ['icon' => '📊', 'label' => 'Formation Google Analytics',           'category' => 'Formation',   'dayOffset' => 3],
            ['icon' => '📱', 'label' => 'Accès réseaux sociaux d\'entreprise', 'category' => 'IT',          'dayOffset' => 2],
            ['icon' => '🗓',  'label' => 'Calendrier éditorial partagé',        'category' => 'Intégration', 'dayOffset' => 3],
        ],
        'RH' => [
            ['icon' => '🗂',  'label' => 'Accès SIRH',                      'category' => 'IT',        'dayOffset' => 1],
            ['icon' => '⚖️', 'label' => 'Formation droit du travail interne', 'category' => 'Formation', 'dayOffset' => 5],
            ['icon' => '🔒', 'label' => 'Formation RGPD données RH',         'category' => 'Formation', 'dayOffset' => 7],
            ['icon' => '📂', 'label' => 'Accès dossiers du personnel',       'category' => 'Accès',     'dayOffset' => 2],
        ],
        'Finance' => [
            ['icon' => '💰', 'label' => 'Accès logiciel comptable',              'category' => 'IT',        'dayOffset' => 1],
            ['icon' => '📈', 'label' => 'Accès tableaux de bord financiers',     'category' => 'IT',        'dayOffset' => 2],
            ['icon' => '🏦', 'label' => 'Formation procédures de paiement',      'category' => 'Formation', 'dayOffset' => 5],
        ],
        'Ventes' => [
            ['icon' => '📞', 'label' => 'Accès CRM',                           'category' => 'IT',          'dayOffset' => 1],
            ['icon' => '🎯', 'label' => 'Formation produits & services',        'category' => 'Formation',   'dayOffset' => 3],
            ['icon' => '🤝', 'label' => 'Accompagnement commercial terrain',    'category' => 'Formation',   'dayOffset' => 7],
            ['icon' => '📊', 'label' => 'Attribution du portefeuille clients',  'category' => 'Intégration', 'dayOffset' => 10],
        ],
    ];

    // ─────────────────────────────────────────────────────────────────────

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(UtilisateurRepository $utilisateurRepo): Response
    {
        $utilisateurs = $utilisateurRepo->findAll();

        return $this->render('onboarding/index.html.twig', [
            'utilisateurs' => $utilisateurs,
            'departments'  => self::DEPARTMENTS,
        ]);
    }

    // ── Generate (creates/reloads onboarding record + tasks) ─────────────
    #[Route('/generate', name: 'generate', methods: ['POST'])]
    public function generate(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data          = json_decode($request->getContent(), true);
        $utilisateurId = (int) ($data['employeeId'] ?? $data['employee'] ?? 0);
        $dept          = trim($data['department'] ?? '');
        $date          = $data['arrivalDate'] ?? date('Y-m-d');

        if (!$utilisateurId) {
            return $this->json(['error' => 'Veuillez sélectionner un employé.'], Response::HTTP_BAD_REQUEST);
        }
        if (empty($dept)) {
            return $this->json(['error' => 'Veuillez sélectionner un département.'], Response::HTTP_BAD_REQUEST);
        }

        /** @var Utilisateur|null $utilisateur */
        $utilisateur = $em->getRepository(Utilisateur::class)->find($utilisateurId);
        if (!$utilisateur) {
            return $this->json(['error' => 'Employé introuvable.'], Response::HTTP_NOT_FOUND);
        }

        // Reuse existing in_progress record OR create a new one
        $onboarding = $em->getRepository(Onboarding::class)->findOneBy([
            'utilisateur' => $utilisateur,
            'status'      => 'in_progress',
        ]);

        if (!$onboarding) {
            $onboarding = new Onboarding();
            $onboarding->setUtilisateur($utilisateur);
            $onboarding->setStatus('in_progress');
            $onboarding->setStartedAt(new \DateTime());
        }

        $onboarding->setDepartement($dept);
        $onboarding->setArrivalDate(new \DateTime($date));

        // Preserve existing tasks by label
        $existingTasks = [];
        foreach ($onboarding->getTasks() as $old) {
            $existingTasks[$old->getLabel()] = $old;
        }

        $taskTemplates = $this->buildTaskTemplates($dept);
        
        foreach ($taskTemplates as $tpl) {
            $label = $tpl['label'];
            if (isset($existingTasks[$label])) {
                // Task already exists, keep it
                unset($existingTasks[$label]);
            } else {
                // New task
                $task = new OnboardingTask();
                $task->setLabel($label);
                $task->setIsDone(false);
                $onboarding->addTask($task);
                $em->persist($task);
            }
        }

        // Remove old tasks that are no longer in the templates
        foreach ($existingTasks as $old) {
            $onboarding->removeTask($old);
            $em->remove($old);
        }

        $em->persist($onboarding);
        $em->flush();

        $tasks = [];
        $i     = 0;
        
        // Ensure the returned tasks are in the order of the templates
        foreach ($taskTemplates as $tpl) {
            $label = $tpl['label'];
            $matchingTask = null;
            foreach ($onboarding->getTasks() as $t) {
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
        $email = $utilisateur->getEmail() ?? '—';
        $role  = $utilisateur->getPosteActuel() ?? 'Nouveau collaborateur';

        return $this->json([
            'onboardingId' => $onboarding->getId(),
            'tasks'        => $tasks,
            'employeeInfo' => [
                'name'        => $name,
                'department'  => $dept,
                'email'       => $email,
                'role'        => $role,
                'arrivalDate' => $date,
            ],
            'autoActions'   => $this->buildAutoActions($name, $dept, $email),
            'postPublished' => $onboarding->isPostPublished(),
        ]);
    }

    // ── Toggle task done/undone ───────────────────────────────────────────
    #[Route('/task/{id}/toggle', name: 'task_toggle', methods: ['POST'])]
    public function toggleTask(int $id, EntityManagerInterface $em): JsonResponse
    {
        $task = $em->getRepository(OnboardingTask::class)->find($id);
        if (!$task) {
            return $this->json(['error' => 'Tâche introuvable.'], Response::HTTP_NOT_FOUND);
        }

        $task->setIsDone(!$task->isDone());

        $onboarding = $task->getOnboarding();
        $allDone    = true;
        foreach ($onboarding->getTasks() as $t) {
            if (!$t->isDone()) { $allDone = false; break; }
        }

        if ($allDone) {
            $onboarding->setStatus('completed');
            $onboarding->setCompletedAt(new \DateTime());
        } else {
            if ($onboarding->getStatus() === 'completed') {
                $onboarding->setStatus('in_progress');
                $onboarding->setCompletedAt(null);
            }
        }

        $em->flush();

        return $this->json([
            'isDone'  => $task->isDone(),
            'allDone' => $allDone,
            'onboarding' => ['status' => $onboarding->getStatus()],
        ]);
    }

    // ── Mark all tasks done/reset ─────────────────────────────────────────
    #[Route('/{id}/mark-all', name: 'mark_all', methods: ['POST'])]
    public function markAll(int $id, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $onboarding = $em->getRepository(Onboarding::class)->find($id);
        if (!$onboarding) {
            return $this->json(['error' => 'Onboarding introuvable.'], Response::HTTP_NOT_FOUND);
        }

        $done = (bool) (json_decode($request->getContent(), true)['done'] ?? true);

        foreach ($onboarding->getTasks() as $task) {
            $task->setIsDone($done);
        }

        $onboarding->setStatus($done ? 'completed' : 'in_progress');
        $onboarding->setCompletedAt($done ? new \DateTime() : null);

        $em->flush();

        return $this->json(['success' => true, 'done' => $done]);
    }

    // ── Publish post (professional announcement) ──────────────────────────
    #[Route('/{id}/publish-post', name: 'publish_post', methods: ['POST'])]
    public function publishPost(int $id, EntityManagerInterface $em): JsonResponse
    {
        $onboarding = $em->getRepository(Onboarding::class)->find($id);
        if (!$onboarding) {
            return $this->json(['error' => 'Onboarding introuvable.'], Response::HTTP_NOT_FOUND);
        }

        if ($onboarding->isPostPublished()) {
            return $this->json(['error' => 'Un post a déjà été publié pour cet onboarding.'], Response::HTTP_CONFLICT);
        }

        $utilisateur = $onboarding->getUtilisateur();
        $name        = trim(($utilisateur->getPrenom() ?? '') . ' ' . ($utilisateur->getNom() ?? ''));
        $dept        = $onboarding->getDepartement() ?? ($utilisateur->getDepartement() ?? '—');
        $role        = $utilisateur->getPosteActuel() ?? 'Nouveau collaborateur';
        $arrival     = $onboarding->getArrivalDate()
            ? $onboarding->getArrivalDate()->format('d/m/Y')
            : date('d/m/Y');

        $contenu = sprintf(
            "🎉 Bienvenue à %s !\n\n"
            . "Nous sommes ravis d'accueillir %s qui rejoint l'équipe %s en tant que %s à compter du %s.\n\n"
            . "Toute l'équipe Humania lui souhaite une excellente intégration et une belle aventure parmi nous ! 🚀\n\n"
            . "#Onboarding #NouveauCollaborateur #Bienvenue",
            $name, $name, $dept, $role, $arrival
        );

        $post = new Publication();
        $post->setContenu($contenu);
        $post->setDateCreation(new \DateTime());
        $post->setStatut('ACTIF');
        $post->setVisibility('PUBLIC');
        $post->setType('onboarding');

        $em->persist($post);

        $onboarding->setPostPublished(true);
        $em->flush();

        return $this->json(['success' => true, 'contenu' => $contenu]);
    }

    // ── Load saved onboarding for an employee ─────────────────────────────
    #[Route('/load/{utilisateurId}', name: 'load', methods: ['GET'])]
    public function load(int $utilisateurId, EntityManagerInterface $em): JsonResponse
    {
        $utilisateur = $em->getRepository(Utilisateur::class)->find($utilisateurId);
        if (!$utilisateur) {
            return $this->json(['onboarding' => null]);
        }

        $onboarding = $em->getRepository(Onboarding::class)->findOneBy(
            ['utilisateur' => $utilisateur],
            ['startedAt'   => 'DESC']
        );

        if (!$onboarding) {
            return $this->json(['onboarding' => null]);
        }

        $tasks = [];
        foreach ($onboarding->getTasks() as $task) {
            $tasks[] = [
                'id'     => $task->getId(),
                'label'  => $task->getLabel(),
                'isDone' => $task->isDone(),
            ];
        }

        $name = trim(($utilisateur->getPrenom() ?? '') . ' ' . ($utilisateur->getNom() ?? ''));

        return $this->json([
            'onboarding' => [
                'id'            => $onboarding->getId(),
                'status'        => $onboarding->getStatus(),
                'postPublished' => $onboarding->isPostPublished(),
                'department'    => $onboarding->getDepartement(),
                'arrivalDate'   => $onboarding->getArrivalDate()?->format('Y-m-d'),
                'tasks'         => $tasks,
                'employeeInfo'  => [
                    'name'       => $name,
                    'department' => $onboarding->getDepartement() ?? $utilisateur->getDepartement() ?? '—',
                    'email'      => $utilisateur->getEmail() ?? '—',
                    'role'       => $utilisateur->getPosteActuel() ?? 'Nouveau collaborateur',
                ],
            ],
        ]);
    }

    // ── Complete onboarding (activate employee) ───────────────────────────
    #[Route('/complete/{id}', name: 'complete', methods: ['POST'])]
    public function completeOnboarding(Employe $employee, EntityManagerInterface $entityManager): JsonResponse
    {
        $employee->setStatus('active');
        $entityManager->flush();

        $this->dispatcher->dispatch(new EmployeeActivatedEvent($employee));

        return $this->json([
            'success' => true,
            'status'  => $employee->getStatus(),
        ]);
    }

    // ── Private helpers ───────────────────────────────────────────────────

    private function buildTaskTemplates(string $dept): array
    {
        $tasks = self::COMMON_TASKS;

        foreach ((self::DEPT_TASKS[$dept] ?? []) as $task) {
            $tasks[] = $task;
        }

        usort($tasks, fn($a, $b) => $a['dayOffset'] <=> $b['dayOffset']);

        return $tasks;
    }

    private function buildAutoActions(string $employee, string $dept, string $email): array
    {
        $login = explode('@', $email)[0];

        $actions = [
            ['icon' => '✉️', 'label' => 'Email de bienvenue envoyé',       'detail' => 'à ' . $email,                         'color' => 'green'],
            ['icon' => '🔐', 'label' => 'Compte utilisateur créé',          'detail' => 'login : ' . $login,                   'color' => 'blue'],
            ['icon' => '🪪', 'label' => 'Badge programmé',                  'detail' => 'Valide à partir de J+0',               'color' => 'yellow'],
            ['icon' => '📋', 'label' => 'Dossier RH initialisé',           'detail' => 'Pièces manquantes à compléter',        'color' => 'purple'],
            ['icon' => '📅', 'label' => 'Agenda J+1 planifié',             'detail' => 'Réunion d\'accueil avec manager',      'color' => 'green'],
        ];

        if (in_array($dept, ['Ingénierie', 'RH', 'Finance'], true)) {
            $actions[] = ['icon' => '💻', 'label' => 'Accès VPN configuré', 'detail' => 'Identifiants envoyés par email', 'color' => 'slate'];
        }

        return $actions;
    }
}
