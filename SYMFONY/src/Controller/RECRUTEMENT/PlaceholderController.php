<?php

namespace App\Controller\RECRUTEMENT;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PlaceholderController extends AbstractController
{
    private const SECTIONS = [
        'utilisateurs' => 'Gestion des utilisateurs',
        'candidats-acceptes' => 'Candidats acceptés',
        'archives' => 'Archives',
        'reseaux-sociaux' => 'Réseaux sociaux',
        'competences' => 'Compétences',
    ];

    #[Route(
        '/a-venir/{section}',
        name: 'app_placeholder',
        requirements: ['section' => 'utilisateurs|candidats-acceptes|archives|reseaux-sociaux|competences']
    )]
    public function __invoke(string $section): Response
    {
        if (!isset(self::SECTIONS[$section])) {
            throw $this->createNotFoundException();
        }

        return $this->render('RECRUTEMENT/placeholder/index.html.twig', [
            'page_title' => self::SECTIONS[$section],
        ]);
    }
}
