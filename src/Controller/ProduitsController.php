<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class ProduitsController extends AbstractController
{
    #[Route('/produits', name: 'app_produits')]
    public function index(): Response
    {
        // Exemple de tableau de produits (id, name, prix)
        $produits = [
            ['name' => 'Produit 1', 'prix' => 30],
            ['name' => 'Produit 2', 'prix' => 25],
            ['name' => 'Produit 3', 'prix' => 40],
        ];

        return $this->render('Page/produits.html.twig', [
            'produits' => $produits,
        ]);
    }
}

