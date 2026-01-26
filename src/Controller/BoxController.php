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

    /**
     * Affiche la page des boxes avec :
     * - La box personnalisable en vedette
     * - Les boxes fixes disponibles
     * - Les produits pour composer la box personnalisable
     */
    #[Route('/box', name: 'app_box')]
    public function index(
        BoxRepository $boxRepository,
        ProduitRepository $produitRepository
    ): Response
    {
        // ========== BOXES FIXES ==========
        // Récupère toutes les boxes fixes (non personnalisables)
        // avec leurs produits associés (jointure pour éviter N+1)
        $boxesFixes = $boxRepository->createQueryBuilder('b')
            ->leftJoin('b.produits', 'p')
            ->addSelect('p')
            ->where('b.type = :type')
            ->andWhere('b.createur IS NULL')
            ->setParameter('type', 'fixe')
            ->orderBy('b.nom', 'ASC')
            ->getQuery()
            ->getResult();

        // ========== BOX PERSONNALISABLE ==========
        // Récupère le template de box personnalisable
        // (une seule box personnalisable par site)
        $boxPersonnalisable = $boxRepository->createQueryBuilder('b')
            ->where('b.type = :type')
            ->andWhere('b.createur IS NULL')
            ->setParameter('type', 'personnalisable')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        // ========== PRODUITS DISPONIBLES ==========
        // Récupère tous les produits disponibles pour composer la box
        // Retourne des objets Produit (pas d'array) pour Twig
        $produits = $produitRepository->createQueryBuilder('p')
            ->where('p.disponible = :disponible')
            ->setParameter('disponible', true)
            ->orderBy('p.name', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->render('Page/box.html.twig', [
            'boxes_fixes' => $boxesFixes,
            'box_personnalisable' => $boxPersonnalisable,
            'produits' => $produits,
        ]);
    }
}