<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class FullCalendarController extends AbstractController
{
    #[Route('/full/calendar', name: 'app_full_calendar')]
    public function index(): Response
    {
    return $this->render('home/composant/calendar.html.twig', [
        // passe ici les variables nécessaires si besoin
    ]);
}
}
