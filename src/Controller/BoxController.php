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
        // On utilise leftJoin + addSelect pour charger les produits EN UNE SEULE REQUÊTE
        $boxesFixes = $boxRepository->createQueryBuilder('b')
            ->leftJoin('b.produits', 'p')
            ->addSelect('p') // Charge tous les champs des produits
            ->where('b.type = :type')
            ->andWhere('b.createur IS NULL')
            ->setParameter('type', 'fixe')
            ->orderBy('b.nom', 'ASC')
            ->getQuery()
            ->getResult();

        // ========== RÉCUPÉRER LA BOX PERSONNALISABLE (SANS produits) ==========
        $boxPersonnalisable = $boxRepository->createQueryBuilder('b')
            ->where('b.type = :type')
            ->andWhere('b.createur IS NULL')
            ->setParameter('type', 'personnalisable')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        // ========== RÉCUPÉRER LES PRODUITS DISPONIBLES ==========
        // ⚠️ CRITIQUE : On retourne un ARRAY au lieu d'objets Produit complets
        // Ça évite que Twig charge les relations boxes → produits → boxes...
        $produits = $produitRepository->createQueryBuilder('p')
            ->select('p.id', 'p.name', 'p.prix', 'p.image', 'p.stock', 'p.description')
            ->where('p.disponible = :disponible')
            ->setParameter('disponible', true)
            ->orderBy('p.name', 'ASC')
            ->getQuery()
            ->getArrayResult(); // ← ARRAY au lieu d'objets !

        return $this->render('Page/box.html.twig', [
            'boxes_fixes' => $boxesFixes,
            'box_personnalisable' => $boxPersonnalisable,
            'produits' => $produits, // Tableau simple, pas d'entités Doctrine
        ]);
    }
}
