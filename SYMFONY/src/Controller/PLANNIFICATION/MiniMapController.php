<?php

namespace App\Controller\PLANNIFICATION;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/minimap')]
final class MiniMapController extends AbstractController
{
    #[Route(name: 'app_minimap_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('minimap/index.html.twig');
    }
}
