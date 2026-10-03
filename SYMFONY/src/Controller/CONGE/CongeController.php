<?php

namespace App\Controller\CONGE;

use App\Entity\Conge;
use App\Entity\Type_conge;
use App\Entity\Utilisateur;
use App\Form\CONGE\CongeType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/conge')]
class CongeController extends AbstractController
{
    #[Route('/', name: 'app_conge_index', methods: ['GET', 'POST'])]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $listAllConges = $this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_RH');
        $canChooseStatut = $listAllConges || $this->isGranted('ROLE_MANAGER');
        $conge = new Conge();
        $formNew = $this->createForm(CongeType::class, $conge, [
            'can_choose_statut' => $canChooseStatut,
        ]);
        $formNew->handleRequest($request);

        if ($formNew->isSubmitted() && $formNew->isValid()) {
            if (!$canChooseStatut) {
                $conge->setStatut('En attente');
            }

            if ($user instanceof Utilisateur) {
                if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_RH')) {
                    $uid = $conge->getUtilisateurId();
                    if ($uid === null || $uid === 0) {
                        $conge->setUtilisateurId($user->getId());
                    }
                } else {
                    $conge->setUtilisateurId($user->getId());
                }
            } elseif (!$conge->getUtilisateurId()) {
                $conge->setUtilisateurId(1);
            }

            $this->calculerNbrJours($conge);

            try {
                $em->persist($conge);
                $em->flush();
                $this->addFlash('success', '✅ Congé créé avec succès !');
                return $this->redirectToRoute('app_conge_index');
            } catch (\Exception $e) {
                $this->addFlash('error', '❌ Erreur BDD : ' . $e->getMessage());
            }
        }

        $repoConge = $em->getRepository(Conge::class);
        $orderDesc = ['id' => 'DESC'];

        if (!$user || !($user instanceof Utilisateur)) {
            $conges = $repoConge->findBy([], $orderDesc);
        } elseif ($listAllConges) {
            $conges = $repoConge->findBy([], $orderDesc);
        } elseif ($this->isGranted('ROLE_MANAGER')) {
            $userDept = $user->getDepartement();
            if ($userDept === null || trim((string) $userDept) === '') {
                $conges = $repoConge->findBy(['utilisateurId' => $user->getId()], $orderDesc);
            } else {
                $conges = $em->createQueryBuilder()
                    ->select('c')
                    ->from(Conge::class, 'c')
                    ->join(Utilisateur::class, 'u', 'WITH', 'c.utilisateurId = u.id')
                    ->where('u.departement = :dept')
                    ->setParameter('dept', $userDept)
                    ->orderBy('c.id', 'DESC')
                    ->getQuery()
                    ->getResult();
            }
        } else {
            $conges = $em->createQueryBuilder()
                ->select('c')
                ->from(Conge::class, 'c')
                ->where('c.utilisateurId = :uid')
                ->setParameter('uid', $user->getId())
                ->orderBy('c.id', 'DESC')
                ->getQuery()
                ->getResult();
        }

        return $this->render('CONGE/conges/index.html.twig', [
            'conges'     => $conges,
            'typeConges' => $em->getRepository(Type_conge::class)->findAll(),
            'formNew'    => $formNew->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_conge_show', methods: ['GET'])]
    public function show(Conge $conge, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if ($user instanceof Utilisateur && !$this->canAccessConge($user, $conge, $em)) {
            $this->addFlash('error', '❌ Vous ne pouvez pas voir ce congé.');
            return $this->redirectToRoute('app_conge_index');
        }
        
        $typeConge = $conge->getTypeCongeId()
            ? $em->getRepository(Type_conge::class)->find($conge->getTypeCongeId())
            : null;

        return $this->render('CONGE/conges/show.html.twig', [
            'conge'     => $conge,
            'typeConge' => $typeConge,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_conge_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Conge $conge, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if ($user instanceof Utilisateur && !$this->canAccessConge($user, $conge, $em)) {
            $this->addFlash('error', '❌ Vous ne pouvez pas modifier ce congé.');
            return $this->redirectToRoute('app_conge_index');
        }

        $canChooseStatut = $this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_RH') || $this->isGranted('ROLE_MANAGER');
        $statutOriginal = $conge->getStatut();
        $form = $this->createForm(CongeType::class, $conge, [
            'can_choose_statut' => $canChooseStatut,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$canChooseStatut) {
                $conge->setStatut($statutOriginal);
            }

            $this->calculerNbrJours($conge);

            try {
                $em->flush();
                $this->addFlash('success', '✅ Congé modifié avec succès !');
                return $this->redirectToRoute('app_conge_index');
            } catch (\Exception $e) {
                $this->addFlash('error', '❌ Erreur BDD : ' . $e->getMessage());
            }
        }

        $congeRequesterLabel = $this->resolveCongeRequesterLabel($em, $conge->getUtilisateurId());

        return $this->render('CONGE/conges/edit.html.twig', [
            'conge'               => $conge,
            'form'                => $form->createView(),
            'congeRequesterLabel' => $congeRequesterLabel,
        ]);
    }

    #[Route('/{id}/archive', name: 'app_conge_archive', methods: ['POST'])]
    public function archive(Request $request, Conge $conge, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if ($user instanceof Utilisateur && !$this->canAccessConge($user, $conge, $em)) {
            $this->addFlash('error', '❌ Action non autorisée.');
            return $this->redirectToRoute('app_conge_index');
        }
        
        if ($this->isCsrfTokenValid('archive' . $conge->getId(), $request->request->get('_token'))) {
            $conge->setStatut('Archivé');
            $em->flush();
            $this->addFlash('success', '📦 Congé archivé.');
        }
        return $this->redirectToRoute('app_conge_index');
    }

    #[Route('/{id}/delete', name: 'app_conge_delete', methods: ['POST'])]
    public function delete(Request $request, Conge $conge, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        
        if ($user instanceof Utilisateur && !$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_RH')) {
            $this->addFlash('error', '❌ Action non autorisée.');
            return $this->redirectToRoute('app_conge_index');
        }
        
        if ($this->isCsrfTokenValid('delete' . $conge->getId(), $request->getPayload()->getString('_token'))) {
            $em->remove($conge);
            $em->flush();
            $this->addFlash('success', '🗑️ Congé supprimé.');
        }
        return $this->redirectToRoute('app_conge_index');
    }

    private function calculerNbrJours(Conge $conge): void
    {
        if ($conge->getDateDebut() && $conge->getDateFin()) {
            $diff = $conge->getDateDebut()->diff($conge->getDateFin());
            $conge->setNbrJours(max(1, $diff->days + 1));
        }
    }

    /** Admin : tout ; employé : ses congés ; manager : les siens + même département que le demandeur. */
    private function canAccessConge(Utilisateur $user, Conge $conge, EntityManagerInterface $em): bool
    {
        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_RH')) {
            return true;
        }
        $ownerId = $conge->getUtilisateurId();
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

    private function resolveCongeRequesterLabel(EntityManagerInterface $em, ?int $utilisateurId): ?string
    {
        if ($utilisateurId === null) {
            return null;
        }
        $u = $em->getRepository(Utilisateur::class)->find($utilisateurId);
        if (!$u) {
            return null;
        }
        $label = trim(($u->getPrenom() ?? '') . ' ' . ($u->getNom() ?? ''));

        return $label !== '' ? $label : null;
    }
}