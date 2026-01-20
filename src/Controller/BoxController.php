<?php

namespace App\Controller;

use App\Repository\BoxRepository;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BoxController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('/box', name: 'app_box')]
    public function index(
        BoxRepository $boxRepository,
        ProduitRepository $produitRepository
    ): Response
    {
        // ========== RÉCUPÉRER LES BOXES FIXES ==========
        $boxesFixes = $boxRepository->createQueryBuilder('b')
            ->leftJoin('b.produits', 'p')
            ->addSelect('p')
            ->where('b.type = :type')
            ->andWhere('b.createur IS NULL')
            ->setParameter('type', 'fixe')
            ->orderBy('b.nom', 'ASC')
            ->getQuery()
            ->getResult();

        // ========== RÉCUPÉRER LA BOX PERSONNALISABLE ==========
        $boxPersonnalisable = $boxRepository->createQueryBuilder('b')
            ->where('b.type = :type')
            ->andWhere('b.createur IS NULL')
            ->setParameter('type', 'personnalisable')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        // ========== RÉCUPÉRER LES PRODUITS DISPONIBLES ==========
        // ✅ On retourne des OBJETS Produit pour que Twig fonctionne
        $produits = $produitRepository->createQueryBuilder('p')
            ->where('p.disponible = :disponible')
            ->setParameter('disponible', true)
            ->orderBy('p.name', 'ASC')
            ->getQuery()
            ->getResult(); // ← OBJETS Produit au lieu d'array !

        return $this->render('Page/box.html.twig', [
            'boxes_fixes' => $boxesFixes,
            'box_personnalisable' => $boxPersonnalisable,
            'produits' => $produits,
        ]);
    }
}