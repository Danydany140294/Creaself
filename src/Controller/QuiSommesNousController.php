<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class QuiSommesNousController extends AbstractController
{
#[Route('/quisommesnous', name: 'app_quisommesnous')]
    public function index(): Response
    {
        return $this->render('Page/qui_sommes_nous.html.twig', [
            'controller_name' => 'QuiSommesNousController',
        ]);
    }
}
