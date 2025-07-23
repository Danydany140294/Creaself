<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AtelierController extends AbstractController
{
    #[Route('/atelier', name: 'app_atelier')]
    public function index(): Response
    {
        return $this->render('atelier/atelier.html.twig', [
            'controller_name' => 'AtelierController',
        ]);
    }
}
