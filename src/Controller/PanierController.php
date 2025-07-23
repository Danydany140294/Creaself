<?php

namespace App\Controller;

use App\Repository\ProduitRepository;
use App\Repository\BoxRepository;
use App\Repository\AtelierRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PanierController extends AbstractController
{
    #[Route('/panier', name: 'app_panier')]
    public function index(
        ProduitRepository $produitRepository,
        BoxRepository $boxRepository,
        AtelierRepository $atelierRepository
    ): Response
    {
        // Récupérer toutes les entités en base
        $produits = $produitRepository->findAll();
        $boxes = $boxRepository->findAll();
        $ateliers = $atelierRepository->findAll();

        // Passer les données à la vue Twig
        return $this->render('panier/panier.html.twig', [
            'produits' => $produits,
            'boxes' => $boxes,
            'ateliers' => $ateliers,
        ]);
    }
}
