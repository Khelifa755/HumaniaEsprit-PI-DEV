<?php

namespace App\Controller\PLANNIFICATION;

use App\Entity\Espaces;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/espaces')]
final class EspacesController extends AbstractController
{
    // ── LIST ──────────────────────────────────────────────────────────────────

    #[Route(name: 'app_espaces_index', methods: ['GET'])]
    public function index(EntityManagerInterface $em): Response
    {
        return $this->render('PLANNIFICATION/espaces/index.html.twig', [
            'espaces' => $em->getRepository(Espaces::class)->findBy([], ['id' => 'ASC'], 99),
        ]);
    }

    // ── CREATE ────────────────────────────────────────────────────────────────

    #[Route('/new', name: 'app_espaces_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $espace = new Espaces();
        $this->hydrate($espace, $request);
        $em->persist($espace);
        $em->flush();

        $this->addFlash('success', 'Espace « ' . $espace->getNom() . ' » créé avec succès.');
        return $this->redirectToRoute('app_espaces_index', [], Response::HTTP_SEE_OTHER);
    }

    // ── EDIT ──────────────────────────────────────────────────────────────────

    #[Route('/{id}/edit', name: 'app_espaces_edit', methods: ['POST'])]
    public function edit(Request $request, Espaces $espace, EntityManagerInterface $em): Response
    {
        $this->hydrate($espace, $request);
        $em->flush();

        $this->addFlash('success', 'Espace « ' . $espace->getNom() . ' » mis à jour.');
        return $this->redirectToRoute('app_espaces_index', [], Response::HTTP_SEE_OTHER);
    }

    // ── DELETE ────────────────────────────────────────────────────────────────

    // Same path as show ({id}) to preserve the original route name/URL structure.
    #[Route('/{id}', name: 'app_espaces_delete', methods: ['POST'])]
    public function delete(Request $request, Espaces $espace, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $espace->getId(), $request->getPayload()->getString('_token'))) {
            $nom = $espace->getNom();
            $em->remove($espace);
            $em->flush();
            $this->addFlash('success', 'Espace « ' . $nom . ' » supprimé.');
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('app_espaces_index', [], Response::HTTP_SEE_OTHER);
    }

    // ── Hydration helper ──────────────────────────────────────────────────────

    private function hydrate(Espaces $espace, Request $r): void
    {
        $espace->setNom(trim($r->request->get('nom', '')));
        $espace->setTypeEspace($r->request->get('typeEspace', 'SALLE_REUNION'));
        $espace->setCapacite((int) $r->request->get('capacite', 10));
        $espace->setEtage((int) $r->request->get('etage', 0));
        $espace->setUrlImage($r->request->get('urlImage') ?: null);
        $espace->setDisponible((bool) $r->request->get('disponible'));
        $espace->setListeEquipements($r->request->get('listeEquipements', ''));
    }
}
