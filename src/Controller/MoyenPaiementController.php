<?php

namespace App\Controller;

use App\Entity\MoyenPaiement;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/mon-compte/moyens-paiement')]
#[IsGranted('ROLE_USER')]
class MoyenPaiementController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    #[Route('/definir-defaut/{id}', name: 'app_moyen_paiement_set_default', methods: ['POST'])]
    public function setDefault(MoyenPaiement $moyenPaiement): JsonResponse
    {
        $user = $this->getUser();
        
        // Vérifier que le moyen de paiement appartient bien à l'utilisateur
        if ($moyenPaiement->getUser() !== $user) {
            return $this->json(['success' => false, 'error' => 'Non autorisé'], 403);
        }
        
        // Retirer le défaut des autres moyens
        foreach ($user->getMoyensPaiement() as $mp) {
            $mp->setParDefaut(false);
        }
        
        // Définir celui-ci comme défaut
        $moyenPaiement->setParDefaut(true);
        $this->em->flush();
        
        return $this->json(['success' => true]);
    }
    
    #[Route('/supprimer/{id}', name: 'app_moyen_paiement_delete', methods: ['DELETE'])]
    public function delete(MoyenPaiement $moyenPaiement): JsonResponse
    {
        $user = $this->getUser();
        
        // Vérifier que le moyen de paiement appartient bien à l'utilisateur
        if ($moyenPaiement->getUser() !== $user) {
            return $this->json(['success' => false, 'error' => 'Non autorisé'], 403);
        }
        
        $this->em->remove($moyenPaiement);
        $this->em->flush();
        
        return $this->json(['success' => true]);
    }
    
    #[Route('/ajouter-carte', name: 'app_moyen_paiement_add_card', methods: ['POST'])]
    public function addCard(): JsonResponse
    {
        // TODO: Intégrer avec Stripe pour ajouter une carte
        // Cette méthode sera implémentée dans la prochaine étape
        
        return $this->json([
            'success' => false,
            'message' => 'Fonctionnalité à venir'
        ]);
    }
    
    #[Route('/configurer-apple-pay', name: 'app_moyen_paiement_add_apple_pay', methods: ['POST'])]
    public function addApplePay(): JsonResponse
    {
        // TODO: Intégrer Apple Pay
        // Cette méthode sera implémentée dans la prochaine étape
        
        return $this->json([
            'success' => false,
            'message' => 'Fonctionnalité à venir'
        ]);
    }
}