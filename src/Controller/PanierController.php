<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Entity\Box;
use App\Service\PanierService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/panier')]
class PanierController extends AbstractController
{
    public function __construct(
        private PanierService $panierService,
        private EntityManagerInterface $entityManager
    ) {}

    /**
     * Affiche le panier
     */
    #[Route('', name: 'app_panier_index', methods: ['GET'])]
    public function index(): Response
    {
        $panier = $this->panierService->getPanier();
        
        return $this->render('panier/index.html.twig', [
            'panier' => $panier,
        ]);
    }

    /**
     * Ajoute un produit au panier
     */
    #[Route('/ajouter-produit/{id}', name: 'app_panier_ajouter_produit', methods: ['POST'])]
    public function ajouterProduit(Produit $produit, Request $request): Response
    {
        $quantite = (int) $request->request->get('quantite', 1);
        
        if ($quantite < 1) {
            $this->addFlash('error', 'La quantité doit être d\'au moins 1.');
            return $this->redirectToReferer($request, 'app_home');
        }

        try {
            $this->panierService->ajouterProduit($produit, $quantite);
            $this->addFlash('success', sprintf('"%s" ajouté au panier !', $produit->getName()));
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToReferer($request, 'app_home');
    }

    /**
     * Ajoute une box au panier
     */
    #[Route('/ajouter-box/{id}', name: 'app_panier_ajouter_box', methods: ['POST'])]
    public function ajouterBox(Box $box, Request $request): Response
    {
        $quantite = (int) $request->request->get('quantite', 1);
        
        if ($quantite < 1) {
            $this->addFlash('error', 'La quantité doit être d\'au moins 1.');
            return $this->redirectToReferer($request, 'app_home');
        }

        try {
            $this->panierService->ajouterBox($box, $quantite);
            $this->addFlash('success', sprintf('"%s" ajoutée au panier !', $box->getNom()));
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToReferer($request, 'app_home');
    }


    /**
 * Ajoute une box personnalisable au panier
 */
#[Route('/ajouter-box-personnalisable/{id}', name: 'app_panier_ajouter_box_personnalisable', methods: ['POST'])]
public function ajouterBoxPersonnalisable(Box $box, Request $request): Response
{
    if ($box->getType() !== 'personnalisable') {
        $this->addFlash('error', 'Cette box n\'est pas personnalisable.');
        return $this->redirectToReferer($request, 'app_home');
    }

    // Récupérer les cookies choisis depuis le formulaire
    // Format attendu: cookies[produit_id] = quantite
    $cookiesChoisis = $request->request->all('cookies');
    
    if (empty($cookiesChoisis)) {
        $this->addFlash('error', 'Veuillez sélectionner au moins un cookie.');
        return $this->redirectToReferer($request, 'app_home');
    }

    // Filtrer et convertir en entiers
    $cookiesChoisis = array_filter(
        array_map('intval', $cookiesChoisis),
        fn($quantite) => $quantite > 0
    );

    try {
        $this->panierService->ajouterBoxPersonnalisable($box, $cookiesChoisis);
        $this->addFlash('success', 'Box personnalisée ajoutée au panier ! 🎉');
    } catch (\Exception $e) {
        $this->addFlash('error', $e->getMessage());
    }

    return $this->redirectToReferer($request, 'app_home');
}

    /**
     * Modifie la quantité d'un élément du panier
     */
    #[Route('/modifier/{type}/{id}', name: 'app_panier_modifier', methods: ['POST'])]
    public function modifier(string $type, int $id, Request $request): Response
    {
        $quantite = (int) $request->request->get('quantite', 1);

        if ($quantite < 1) {
            $this->addFlash('error', 'La quantité doit être d\'au moins 1.');
            return $this->redirectToRoute('app_panier_index');
        }

        try {
            $this->panierService->modifierQuantite($type, $id, $quantite);
            $this->addFlash('success', 'Quantité modifiée avec succès !');
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('app_panier_index');
    }

    /**
     * Retire un élément du panier
     */
    #[Route('/retirer/{type}/{id}', name: 'app_panier_retirer', methods: ['POST', 'GET'])]
    public function retirer(string $type, int $id, Request $request): Response
    {
        try {
            $this->panierService->retirerElement($type, $id);
            $this->addFlash('success', 'Élément retiré du panier.');
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('app_panier_index');
    }

    /**
     * Vide complètement le panier
     */
    #[Route('/vider', name: 'app_panier_vider', methods: ['POST'])]
    public function vider(): Response
    {
        try {
            $this->panierService->viderPanier();
            $this->addFlash('success', 'Panier vidé avec succès.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue lors de la suppression du panier.');
        }

        return $this->redirectToRoute('app_panier_index');
    }

    /**
     * Redirige vers la page précédente ou une route par défaut
     */
    private function redirectToReferer(Request $request, string $defaultRoute = 'app_home'): Response
    {
        $referer = $request->headers->get('referer');
        
        if ($referer) {
            return $this->redirect($referer);
        }
        
        return $this->redirectToRoute($defaultRoute);
    }
}