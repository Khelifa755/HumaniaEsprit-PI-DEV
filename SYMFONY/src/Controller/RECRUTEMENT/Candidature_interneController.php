<?php

namespace App\Controller\RECRUTEMENT;

use App\Entity\Candidature_interne;
use App\Form\RECRUTEMENT\Candidature_interneType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/candidature/interne')]
final class Candidature_interneController extends AbstractController
{
    #[Route(name: 'app_candidature_interne_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $candidatureInternes = $entityManager
            ->getRepository(Candidature_interne::class)
            ->findBy([], ['id' => 'ASC'], 99);

        // Form vide pour le modal "Nouvelle demande"
        $createForm = $this->createForm(Candidature_interneType::class, new Candidature_interne(), [
            'action' => $this->generateUrl('app_candidature_interne_new'),
            'method' => 'POST',
        ]);

        return $this->render('RECRUTEMENT/candidature_interne/index.html.twig', [
            'candidature_internes' => $candidatureInternes,
            'form'                 => $createForm,
        ]);
    }

    #[Route('/new', name: 'app_candidature_interne_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $candidatureInterne = new Candidature_interne();
        $form = $this->createForm(Candidature_interneType::class, $candidatureInterne, [
            'action' => $this->generateUrl('app_candidature_interne_new'),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($candidatureInterne);
            $entityManager->flush();
            $this->addFlash('success', 'Candidature créée avec succès.');

            return $this->redirectToRoute('app_candidature_interne_index', [], Response::HTTP_SEE_OTHER);
        }

        // Si la soumission a échoué (validation), on réaffiche l'index avec le formulaire en erreur
        if ($form->isSubmitted() && !$form->isValid()) {
            $candidatureInternes = $entityManager
                ->getRepository(Candidature_interne::class)
                ->findBy([], ['id' => 'ASC'], 99);

            return $this->render('RECRUTEMENT/candidature_interne/index.html.twig', [
                'candidature_internes' => $candidatureInternes,
                'form'                 => $form,
                'open_new_modal'       => true,
            ], new Response('', Response::HTTP_UNPROCESSABLE_ENTITY));
        }

        return $this->redirectToRoute('app_candidature_interne_index');
    }

    #[Route('/{id}', name: 'app_candidature_interne_show', methods: ['GET'])]
    public function show(Candidature_interne $candidatureInterne): Response
    {
        return $this->render('RECRUTEMENT/candidature_interne/show.html.twig', [
            'candidature_interne' => $candidatureInterne,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_candidature_interne_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Candidature_interne $candidatureInterne, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(Candidature_interneType::class, $candidatureInterne, [
            'action' => $this->generateUrl('app_candidature_interne_edit', ['id' => $candidatureInterne->getId()]),
            'method' => 'POST',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Candidature mise à jour avec succès.');

            return $this->redirectToRoute('app_candidature_interne_index', [], Response::HTTP_SEE_OTHER);
        }

        // ── Réponse partielle pour le modal (fetch JS) ──
        if ($request->query->getBoolean('modal')) {
            return $this->render('RECRUTEMENT/candidature_interne/_edit_modal_body.html.twig', [
                'candidature_interne' => $candidatureInterne,
                'form'                => $form,
            ]);
        }

        // ── Page complète (fallback sans JS) ──
        return $this->render('RECRUTEMENT/candidature_interne/edit.html.twig', [
            'candidature_interne' => $candidatureInterne,
            'form'                => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_candidature_interne_delete', methods: ['POST'])]
    public function delete(Request $request, Candidature_interne $candidatureInterne, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $candidatureInterne->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($candidatureInterne);
            $entityManager->flush();
            $this->addFlash('success', 'Candidature supprimée.');
        }

        return $this->redirectToRoute('app_candidature_interne_index', [], Response::HTTP_SEE_OTHER);
    }
}
