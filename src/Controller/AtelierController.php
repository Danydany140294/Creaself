<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class AtelierController extends AbstractController
{
    #[Route('/atelier', name: 'app_atelier')]
    public function index(): Response
    {
        // Exemple de tableau d'ateliers (id, name, prix)
        $ateliers = [
            ['name' => 'Atelier Pâtisserie', 'prix' => 30],
            ['name' => 'Atelier Peinture', 'prix' => 25],
            ['name' => 'Atelier Sculpture', 'prix' => 40],
        ];

        return $this->render('atelier/atelier.html.twig', [
            'ateliers' => $ateliers,
        ]);
    }
}
