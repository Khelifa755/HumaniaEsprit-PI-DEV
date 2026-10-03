<?php

namespace App\Controller\RECRUTEMENT;

use App\Entity\Poste_interne;
use App\Form\RECRUTEMENT\Poste_interneType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/poste/interne')]
final class Poste_interneController extends AbstractController
{
    #[Route(name: 'app_poste_interne_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $posteInternes = $entityManager
            ->getRepository(Poste_interne::class)
            ->findAll();

        $createForm = $this->createForm(Poste_interneType::class, new Poste_interne());

        return $this->render('RECRUTEMENT/poste_interne/index.html.twig', [
            'poste_internes' => $posteInternes,
            'form' => $createForm,
        ]);
    }

    #[Route('/new', name: 'app_poste_interne_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $posteInterne = new Poste_interne();
        $form = $this->createForm(Poste_interneType::class, $posteInterne);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($posteInterne);
            $entityManager->flush();

            return $this->redirectToRoute('app_poste_interne_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('RECRUTEMENT/poste_interne/new.html.twig', [
            'poste_interne' => $posteInterne,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_poste_interne_show', methods: ['GET'])]
    public function show(Poste_interne $posteInterne): Response
    {
        return $this->render('RECRUTEMENT/poste_interne/show.html.twig', [
            'poste_interne' => $posteInterne,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_poste_interne_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Poste_interne $posteInterne, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(Poste_interneType::class, $posteInterne);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_poste_interne_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('RECRUTEMENT/poste_interne/edit.html.twig', [
            'poste_interne' => $posteInterne,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_poste_interne_delete', methods: ['POST'])]
    public function delete(Request $request, Poste_interne $posteInterne, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$posteInterne->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($posteInterne);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_poste_interne_index', [], Response::HTTP_SEE_OTHER);
    }
}
