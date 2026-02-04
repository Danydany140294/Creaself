<?php

namespace App\Controller;

use App\Entity\Adresse;
use App\Form\AdresseType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/compte/adresses')]
#[IsGranted('ROLE_USER')]
class AdresseController extends AbstractController
{
    #[Route('', name: 'app_adresses_index')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        
        // Créer le formulaire d'ajout d'adresse
        $adresse = new Adresse();
        $adresse->setUser($user);
        
        $form = $this->createForm(AdresseType::class, $adresse);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Si l'adresse est définie par défaut, retirer le défaut des autres
            if ($adresse->isParDefaut()) {
                $this->removeDefaultFromOtherAddresses($user, $em);
            }
            
            // Si c'est la première adresse, la mettre par défaut
            if ($user->getAdresses()->count() === 0) {
                $adresse->setParDefaut(true);
            }
            
            // Définir la date de création
            $adresse->setDateCreation(new \DateTime());
            
            $em->persist($adresse);
            $em->flush();
            
            $this->addFlash('success', 'Adresse ajoutée avec succès.');
            
            return $this->redirectToRoute('app_adresses_index');
        }
        
        return $this->render('compte/adresses.html.twig', [
            'addressForm' => $form->createView(),
        ]);
    }
    
    #[Route('/{id}/modifier', name: 'app_adresses_edit', methods: ['GET', 'POST'])]
    public function edit(Adresse $adresse, Request $request, EntityManagerInterface $em): Response
    {
        // Vérifier que l'adresse appartient à l'utilisateur
        if ($adresse->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas modifier cette adresse.');
        }
        
        $form = $this->createForm(AdresseType::class, $adresse);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Si l'adresse est définie par défaut, retirer le défaut des autres
            if ($adresse->isParDefaut()) {
                $this->removeDefaultFromOtherAddresses($this->getUser(), $em);
                $adresse->setParDefaut(true); // Re-set après le remove
            }
            
            $em->flush();
            
            $this->addFlash('success', 'Adresse modifiée avec succès.');
            
            return $this->redirectToRoute('app_adresses_index');
        }
        
        return $this->render('compte/adresses.html.twig', [
            'addressForm' => $form->createView(),
            'editMode' => true,
            'adresseToEdit' => $adresse,
        ]);
    }
    
    #[Route('/{id}/supprimer', name: 'app_adresses_delete', methods: ['DELETE', 'POST', 'GET'])]
    public function delete(Adresse $adresse, Request $request, EntityManagerInterface $em): Response
    {
        // Vérifier que l'adresse appartient à l'utilisateur
        if ($adresse->getUser() !== $this->getUser()) {
            if ($request->isXmlHttpRequest()) {
                return $this->json(['success' => false, 'message' => 'Accès refusé'], 403);
            }
            throw $this->createAccessDeniedException('Vous ne pouvez pas supprimer cette adresse.');
        }
        
        // Empêcher la suppression si c'est l'adresse par défaut et qu'il y a d'autres adresses
        $userAddresses = $this->getUser()->getAdresses();
        if ($adresse->isParDefaut() && count($userAddresses) > 1) {
            $message = 'Vous ne pouvez pas supprimer votre adresse par défaut. Définissez d\'abord une autre adresse comme adresse par défaut.';
            if ($request->isXmlHttpRequest()) {
                return $this->json(['success' => false, 'message' => $message], 400);
            }
            $this->addFlash('warning', $message);
            return $this->redirectToRoute('app_adresses_index');
        }
        
        $wasDefault = $adresse->isParDefaut();
        
        $em->remove($adresse);
        $em->flush();
        
        // Si c'était l'adresse par défaut, en définir une nouvelle
        if ($wasDefault) {
            $autreAdresse = $this->getUser()->getAdresses()->first();
            if ($autreAdresse) {
                $autreAdresse->setParDefaut(true);
                $em->flush();
            }
        }
        
        $message = 'Adresse supprimée avec succès';
        
        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'success' => true,
                'message' => $message
            ]);
        }
        
        $this->addFlash('success', $message);
        return $this->redirectToRoute('app_adresses_index');
    }
    
    #[Route('/{id}/definir-par-defaut', name: 'app_adresses_set_default', methods: ['POST', 'GET'])]
    public function setDefault(Adresse $adresse, Request $request, EntityManagerInterface $em): Response
    {
        // Vérifier que l'adresse appartient à l'utilisateur
        if ($adresse->getUser() !== $this->getUser()) {
            if ($request->isXmlHttpRequest()) {
                return $this->json(['success' => false, 'message' => 'Accès refusé'], 403);
            }
            throw $this->createAccessDeniedException('Vous ne pouvez pas modifier cette adresse.');
        }
        
        // Retirer le défaut des autres adresses
        $this->removeDefaultFromOtherAddresses($this->getUser(), $em);
        
        // Définir cette adresse par défaut
        $adresse->setParDefaut(true);
        $em->flush();
        
        $message = 'Adresse définie par défaut';
        
        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'success' => true,
                'message' => $message
            ]);
        }
        
        $this->addFlash('success', $message);
        return $this->redirectToRoute('app_adresses_index');
    }
    
    /**
     * Retire le statut "par défaut" de toutes les adresses de l'utilisateur
     */
    private function removeDefaultFromOtherAddresses($user, EntityManagerInterface $em): void
    {
        foreach ($user->getAdresses() as $addr) {
            if ($addr->isParDefaut()) {
                $addr->setParDefaut(false);
            }
        }
        $em->flush();
    }
}