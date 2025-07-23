<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class BoxController extends AbstractController
{
    #[Route('/box', name: 'app_box')]
    public function index(): Response
    {
        // Exemple de tableau de boxes
        $boxes = [
            ['name' => 'Box Gourmande', 'prix' => 20],
            ['name' => 'Box Bien-Être', 'prix' => 35],
            ['name' => 'Box Découverte', 'prix' => 25],
        ];

        return $this->render('box/box.html.twig', [
            'boxes' => $boxes,
        ]);
    }
}
