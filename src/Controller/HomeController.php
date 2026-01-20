<?php

namespace App\Controller;

use App\Repository\ProduitRepository;
use App\Repository\BoxRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        ProduitRepository $produitRepository,
        BoxRepository $boxRepository
    ): Response
    {
        // Récupérer les produits disponibles (cookies)
        $produits = $produitRepository->findBy(
            ['disponible' => true],
            ['id' => 'ASC'],
            5 // Limiter à 5 produits pour l'accueil
        );

        // Récupérer les boxes (sauf les personnalisables)
        $boxes = $boxRepository->findBy(
            ['type' => 'fixe'],
            ['id' => 'ASC']
        );

        // Récupérer la box personnalisable
        $boxPersonnalisable = $boxRepository->findOneBy(['type' => 'personnalisable']);

        return $this->render('Page/home.html.twig', [
            'produits' => $produits,
            'boxes' => $boxes,
            'box_personnalisable' => $boxPersonnalisable,
        ]);
    }
}