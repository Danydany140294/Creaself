<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Controller\Admin\UserCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    #[Route('/admin', name: 'admin_dashboard')]
    public function index(): Response
    {
        $userCount = $this->userRepository->count([]);

        // Compte les utilisateurs avec le rôle ADMIN
        $admins = $this->userRepository->findByRole('ROLE_ADMIN');
        $adminsCount = count($admins);

        // Nombre d'utilisateurs standards
        $usersCount = $userCount - $adminsCount;

        // Récupère les 5 derniers utilisateurs créés
        

        return $this->render('admin/dashboard.html.twig', [
            'userCount' => $userCount,
            'adminsCount' => $adminsCount,
            'usersCount' => $usersCount,
            
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

        yield MenuItem::linkToCrud('Utilisateurs', 'fa fa-users', User::class)
            ->setController(UserCrudController::class);
    }
}
