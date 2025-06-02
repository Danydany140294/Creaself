<?php

namespace App\Controller\Admin;

use App\Controller\UserController;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin_dashboard')]
    public function index(): Response
    {
        // Debug temporaire : affiche l'utilisateur et ses rôles
    dump($this->getUser()); // Affiche l'objet User connecté
    dd($this->isGranted('ROLE_ADMIN')); // Vérifie si cet utilisateur a le rôle admin

        // Tu peux rediriger vers un CRUD ou afficher une page custom
        return $this->render('admin/dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Dashboard Admin - CreaSelf');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linktoDashboard('Dashboard', 'fa fa-home');

        // Exemple de lien vers la gestion des utilisateurs (CRUD à créer)
        yield MenuItem::linkToCrud('Utilisateurs', 'fa fa-users', User::class);
    }
}
