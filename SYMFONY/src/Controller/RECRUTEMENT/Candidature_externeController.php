<?php

namespace App\Controller\RECRUTEMENT;

use App\Entity\Candidature_externe;
use App\Form\RECRUTEMENT\Candidature_externeType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/candidature/externe')]
final class Candidature_externeController extends AbstractController
{
    #[Route(name: 'app_candidature_externe_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $candidatureExternes = $entityManager
            ->getRepository(Candidature_externe::class)
            ->findAll();

        $createForm = $this->createForm(Candidature_externeType::class, new Candidature_externe());

        return $this->render('RECRUTEMENT/candidature_externe/index.html.twig', [
            'candidature_externes' => $candidatureExternes,
            'form' => $createForm,
        ]);
    }

    #[Route('/new', name: 'app_candidature_externe_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $candidatureExterne = new Candidature_externe();
        $form = $this->createForm(Candidature_externeType::class, $candidatureExterne);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($candidatureExterne);
            $entityManager->flush();

            return $this->redirectToRoute('app_candidature_externe_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('RECRUTEMENT/candidature_externe/new.html.twig', [
            'candidature_externe' => $candidatureExterne,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_candidature_externe_show', methods: ['GET'])]
    public function show(Candidature_externe $candidatureExterne): Response
    {
        return $this->render('RECRUTEMENT/candidature_externe/show.html.twig', [
            'candidature_externe' => $candidatureExterne,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_candidature_externe_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Candidature_externe $candidatureExterne, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(Candidature_externeType::class, $candidatureExterne);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_candidature_externe_index', [], Response::HTTP_SEE_OTHER);
        }

        if ($request->query->getBoolean('modal')) {
            return $this->render('RECRUTEMENT/candidature_externe/_edit_modal.html.twig', [
                'candidature_externe' => $candidatureExterne,
                'form' => $form,
            ]);
        }

        return $this->render('RECRUTEMENT/candidature_externe/edit.html.twig', [
            'candidature_externe' => $candidatureExterne,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_candidature_externe_delete', methods: ['POST'])]
    public function delete(Request $request, Candidature_externe $candidatureExterne, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$candidatureExterne->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($candidatureExterne);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_candidature_externe_index', [], Response::HTTP_SEE_OTHER);
    }
}
