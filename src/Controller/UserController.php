<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Adresse;
use App\Form\UserForm;
use App\Form\AdresseType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\AdresseService;

#[Route('/user')]
final class UserController extends AbstractController
{
   public function __construct(
    private AdresseService $adresseService
) {} 





/**
     * Liste tous les utilisateurs (admin uniquement)
     */
    #[Route(name: 'app_user_index', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    /**
     * Créer un nouvel utilisateur (admin uniquement)
     */
    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(UserForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    /**
     * Dashboard de l'utilisateur connecté avec gestion des adresses
     * ⚠️ IMPORTANT : Cette route doit être AVANT /{id} pour éviter les conflits
     */
    #[Route('/mon-compte', name: 'app_user_dashboard', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function dashboard(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $adresse = new Adresse();
        $adresse->setUser($user);

        $adresseForm = $this->createForm(AdresseType::class, $adresse);
        $adresseForm->handleRequest($request);

        if ($adresseForm->isSubmitted() && $adresseForm->isValid()) {
            if ($adresse->isParDefaut()) {
                $this->adresseService->removeDefaultFromOtherAddresses($user);
            }

            if ($user->getAdresses()->count() === 0) {
                $adresse->setParDefaut(true);
            }

            $entityManager->persist($adresse);
            $entityManager->flush();

            $this->addFlash('success', 'Adresse ajoutée avec succès.');

            return $this->redirectToRoute('app_user_dashboard');
        }

        return $this->render('user/mon_compte.html.twig', [
            'user' => $user,
            'adresseForm' => $adresseForm->createView(),
        ]);
    }

    /**
     * Afficher un utilisateur spécifique (admin uniquement)
     */
    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * Éditer un utilisateur (admin uniquement)
     */
    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    /**
     * Supprimer un utilisateur (admin uniquement)
     */
    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }

    
}