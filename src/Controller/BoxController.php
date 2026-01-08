<?php

namespace App\Controller;

use App\Repository\BoxRepository;
use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BoxController extends AbstractController
{
    #[Route('/box', name: 'app_box')]
    public function index(
        BoxRepository $boxRepository,
        ProduitRepository $produitRepository
    ): Response
    {
        // Récupérer toutes les boxes fixes
        $boxesFixes = $boxRepository->findBy(
            ['type' => 'fixe'],
            ['nom' => 'ASC']
        );

        // Récupérer la box personnalisable
        $boxPersonnalisable = $boxRepository->findOneBy(['type' => 'personnalisable']);

        // Récupérer tous les produits disponibles (pour la modal)
        $produits = $produitRepository->findBy(
            ['disponible' => true],
            ['name' => 'ASC']
        );

        return $this->render('Page/box.html.twig', [
            'boxes_fixes' => $boxesFixes,
            'box_personnalisable' => $boxPersonnalisable,
            'produits' => $produits,
        ]);
    }
}