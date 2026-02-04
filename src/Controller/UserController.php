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

#[Route('/user')]
final class UserController extends AbstractController
{
    /**
     * Liste tous les utilisateurs (admin)
     */
    #[Route(name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    /**
     * Créer un nouvel utilisateur
     */
    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
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
        
        // Créer le formulaire d'ajout d'adresse
        $adresse = new Adresse();
        $adresse->setUser($user);
        
        $adresseForm = $this->createForm(AdresseType::class, $adresse);
        $adresseForm->handleRequest($request);
        
        // Gérer la soumission du formulaire d'adresse
        if ($adresseForm->isSubmitted() && $adresseForm->isValid()) {
            // Si l'adresse est définie par défaut, retirer le défaut des autres
            if ($adresse->isParDefaut()) {
                $this->removeDefaultFromOtherAddresses($user, $entityManager);
            }
            
            // Si c'est la première adresse, la mettre par défaut automatiquement
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
     * Afficher un utilisateur spécifique
     */
    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * Éditer un utilisateur
     */
    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
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
     * Supprimer un utilisateur
     */
    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * Retire le statut "par défaut" de toutes les adresses de l'utilisateur
     */
    private function removeDefaultFromOtherAddresses(User $user, EntityManagerInterface $entityManager): void
    {
        foreach ($user->getAdresses() as $addr) {
            if ($addr->isParDefaut()) {
                $addr->setParDefaut(false);
            }
        }
        $entityManager->flush();
    }
}