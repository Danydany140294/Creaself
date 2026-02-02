<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\Produit;
use App\Entity\Box;
use App\Entity\Panier;
use App\Entity\Commande;
use App\Entity\LigneCommande;
use App\Entity\LignePanier;
use App\Repository\UserRepository;
use App\Repository\ProduitRepository;
use App\Repository\BoxRepository;
use App\Repository\PanierRepository;
use App\Repository\CommandeRepository;
use App\Controller\Admin\UserCrudController;
use App\Controller\Admin\ProduitCrudController;
use App\Controller\Admin\BoxCrudController;
use App\Controller\Admin\CommandeCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[IsGranted('ROLE_ADMIN')]
class DashboardController extends AbstractDashboardController
{
    private UserRepository $userRepository;
    private ProduitRepository $produitRepository;
    private BoxRepository $boxRepository;
    private PanierRepository $panierRepository;
    private CommandeRepository $commandeRepository;

    public function __construct(
        UserRepository $userRepository,
        ProduitRepository $produitRepository,
        BoxRepository $boxRepository,
        PanierRepository $panierRepository,
        CommandeRepository $commandeRepository
    ) {
        $this->userRepository = $userRepository;
        $this->produitRepository = $produitRepository;
        $this->boxRepository = $boxRepository;
        $this->panierRepository = $panierRepository;
        $this->commandeRepository = $commandeRepository;
    }

    #[Route('/admin', name: 'admin_dashboard')]
    public function index(): Response
    {
        // STATISTIQUES UTILISATEURS
        $userCount = $this->userRepository->count([]);
        $admins = $this->userRepository->findByRole('ROLE_ADMIN');
        $adminsCount = count($admins);
        $usersCount = $userCount - $adminsCount;

        // STATISTIQUES PRODUITS
        $produitsCount = $this->produitRepository->count([]);
        $produitsActifs = $this->produitRepository->count(['isActive' => true]);
        
        // STATISTIQUES BOXES
        $boxesCount = $this->boxRepository->count([]);
        $boxesActives = $this->boxRepository->count(['isActive' => true]);

        // STATISTIQUES PANIERS
        $paniersActifsCount = $this->panierRepository->count([]);

        // STATISTIQUES COMMANDES
        $commandesCount = $this->commandeRepository->count([]);
        $commandesEnCours = $this->commandeRepository->count(['statut' => 'en_cours']);
        $commandesLivrees = $this->commandeRepository->count(['statut' => 'livree']);
        
        // CHIFFRE D'AFFAIRES TOTAL
        $totalCA = 0;
        $commandes = $this->commandeRepository->findAll();
        foreach ($commandes as $commande) {
            $totalCA += $commande->getMontantTotal();
        }

        // DERNIÈRES COMMANDES (5 dernières)
        $dernieresCommandes = $this->commandeRepository->findBy(
            [],
            ['dateCommande' => 'DESC'],
            5
        );

        // DERNIERS UTILISATEURS (5 derniers)
        $derniersUtilisateurs = $this->userRepository->findBy(
            [],
            ['id' => 'DESC'],
            5
        );

        return $this->render('admin/dashboard.html.twig', [
            // Utilisateurs
            'userCount' => $userCount,
            'adminsCount' => $adminsCount,
            'usersCount' => $usersCount,
            'derniersUtilisateurs' => $derniersUtilisateurs,
            
            // Produits
            'produitsCount' => $produitsCount,
            'produitsActifs' => $produitsActifs,
            
            // Boxes
            'boxesCount' => $boxesCount,
            'boxesActives' => $boxesActives,
            
            // Paniers
            'paniersActifsCount' => $paniersActifsCount,
            
            // Commandes
            'commandesCount' => $commandesCount,
            'commandesEnCours' => $commandesEnCours,
            'commandesLivrees' => $commandesLivrees,
            'dernieresCommandes' => $dernieresCommandes,
            
            // CA
            'totalCA' => $totalCA,
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Dashboard Admin - CreaSelf');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linktoDashboard('Dashboard', 'fa fa-home');

        // SECTION UTILISATEURS
        yield MenuItem::section('Gestion Utilisateurs');
        yield MenuItem::linkToCrud('Utilisateurs', 'fa fa-users', User::class)
            ->setController(UserCrudController::class);

        // SECTION PRODUITS
        yield MenuItem::section('Catalogue');
        yield MenuItem::linkToCrud('Cookies', 'fa fa-cookie', Produit::class)
            ->setController(ProduitCrudController::class);
        yield MenuItem::linkToCrud('Boxes', 'fa fa-box', Box::class)
            ->setController(BoxCrudController::class);

        // SECTION VENTES
        yield MenuItem::section('Ventes & Commandes');
        yield MenuItem::linkToCrud('Commandes', 'fa fa-shopping-cart', Commande::class)
            ->setController(CommandeCrudController::class);
        yield MenuItem::linkToCrud('Lignes de Commande', 'fa fa-list', LigneCommande::class);

        // SECTION PANIERS
        yield MenuItem::section('Paniers');
        yield MenuItem::linkToCrud('Paniers Actifs', 'fa fa-shopping-basket', Panier::class);
        yield MenuItem::linkToCrud('Lignes de Panier', 'fa fa-align-justify', LignePanier::class);

        // RETOUR AU SITE
        yield MenuItem::section('Navigation');
        yield MenuItem::linkToRoute('Retour au site', 'fa fa-arrow-left', 'app_home');
    }
}