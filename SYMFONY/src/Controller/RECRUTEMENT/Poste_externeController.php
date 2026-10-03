<?php

namespace App\Controller\RECRUTEMENT;

use App\Entity\Poste_externe;
use App\Form\RECRUTEMENT\Poste_externeType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/poste/externe')]
final class Poste_externeController extends AbstractController
{
    #[Route(name: 'app_poste_externe_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $posteExternes = $entityManager
            ->getRepository(Poste_externe::class)
            ->findAll();

        // Create form is passed to the index so the "New" modal works inline
        $createForm = $this->createForm(Poste_externeType::class, new Poste_externe(), [
            'action' => $this->generateUrl('app_poste_externe_new'),
        ]);

        return $this->render('RECRUTEMENT/poste_externe/index.html.twig', [
            'poste_externes' => $posteExternes,
            'form'           => $createForm,
        ]);
    }

    #[Route('/new', name: 'app_poste_externe_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $posteExterne = new Poste_externe();
        $form = $this->createForm(Poste_externeType::class, $posteExterne);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($posteExterne);
            $entityManager->flush();

            return $this->redirectToRoute('app_poste_externe_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('RECRUTEMENT/poste_externe/new.html.twig', [
            'poste_externe' => $posteExterne,
            'form'          => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_poste_externe_show', methods: ['GET'])]
    public function show(Poste_externe $posteExterne): Response
    {
        return $this->render('RECRUTEMENT/poste_externe/show.html.twig', [
            'poste_externe' => $posteExterne,
        ]);
    }

    /**
     * Edit action.
     *
     * Two rendering modes:
     *   • Normal GET/POST  → renders poste_externe/edit.html.twig  (full page)
     *   • GET ?modal=1     → renders poste_externe/_edit_modal.html.twig (fragment for AJAX)
     *
     * The modal fetch from index.html.twig calls:
     *   /poste/externe/{id}/edit?modal=1
     * which returns only the form HTML, injected into the modal body via JS.
     */
    #[Route('/{id}/edit', name: 'app_poste_externe_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Poste_externe $posteExterne, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(Poste_externeType::class, $posteExterne);
        $form->handleRequest($request);

        // Handle a POST submission (form submitted from either the full page or the modal)
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_poste_externe_index', [], Response::HTTP_SEE_OTHER);
        }

        // AJAX modal request: return only the partial template (no layout)
        if ($request->query->getBoolean('modal')) {
            return $this->render('RECRUTEMENT/poste_externe/_edit_modal.html.twig', [
                'poste_externe' => $posteExterne,
                'form'          => $form,
            ]);
        }

        // Standard full-page render
        return $this->render('RECRUTEMENT/poste_externe/edit.html.twig', [
            'poste_externe' => $posteExterne,
            'form'          => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_poste_externe_delete', methods: ['POST'])]
    public function delete(Request $request, Poste_externe $posteExterne, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $posteExterne->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($posteExterne);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_poste_externe_index', [], Response::HTTP_SEE_OTHER);
    }
}