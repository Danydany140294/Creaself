<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Entity\Box;
use App\Repository\PanierRepository;
use App\Service\PanierService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/panier')]
class PanierController extends AbstractController
{
    public function __construct(
        private PanierService $panierService,
        private EntityManagerInterface $entityManager,
        private PanierRepository $panierRepository,
        private Security $security
    ) {}

    /**
     * Affiche le panier complet
     */
    #[Route('', name: 'app_panier_index', methods: ['GET'])]
    public function index(): Response
    {
        $panier = $this->panierService->getPanier();
        
        // Utilisateur connecté : panier déjà formaté par le service
        if (isset($panier['lignes'])) {
            return $this->render('Page/panier.html.twig', ['panier' => $panier]);
        }
        
        // Visiteur : calculer les totaux pour le panier session
        $panier['total'] = $this->calculerTotal($panier);
        $panier['nombre_articles'] = $this->calculerNombreArticles($panier);
        $panier['is_empty'] = $panier['nombre_articles'] === 0;
        
        return $this->render('Page/panier.html.twig', ['panier' => $panier]);
    }

    /**
     * API : Retourne le panier en JSON
     */
    #[Route('/api/panier', name: 'app_panier_api', methods: ['GET'])]
    public function getPanierApi(): Response
    {
        try {
            $user = $this->security->getUser();
            
            // Utilisateur connecté
            if ($user instanceof \App\Entity\User) {
                return $this->json($this->formatPanierBDD($user));
            }
            
            // Visiteur
            return $this->json($this->formatPanierSession($this->panierService->getPanier()));
            
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
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
            return $this->redirectToReferer($request);
        }

        try {
            $this->panierService->ajouterProduit($produit, $quantite);
            $this->addFlash('success', sprintf('"%s" ajouté au panier !', $produit->getName()));
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToReferer($request);
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
            return $this->redirectToReferer($request);
        }

        try {
            $this->panierService->ajouterBox($box, $quantite);
            $this->addFlash('success', sprintf('"%s" ajoutée au panier !', $box->getNom()));
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToReferer($request);
    }

    /**
     * Ajoute une box personnalisable au panier
     */
    #[Route('/ajouter-box-personnalisable', name: 'app_panier_ajouter_box_personnalisable', methods: ['POST'])]
    public function ajouterBoxPersonnalisable(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        
        if (!$data || !isset($data['cookies']) || empty($data['cookies'])) {
            return $this->json(['success' => false, 'message' => 'Données invalides'], 400);
        }

        // Convertir les clés "produit_X" en IDs numériques
        $cookiesIds = [];
        foreach ($data['cookies'] as $key => $quantite) {
            $produitId = (int) str_replace('produit_', '', $key);
            $cookiesIds[$produitId] = (int) $quantite;
        }

        try {
            $boxTemplate = $this->entityManager->getRepository(Box::class)
                ->findOneBy(['type' => 'personnalisable']);
            
            if (!$boxTemplate) {
                return $this->json(['success' => false, 'message' => 'Box personnalisable non disponible'], 404);
            }

            $this->panierService->ajouterBoxPersonnalisable($boxTemplate, $cookiesIds);
            
            return $this->json(['success' => true, 'message' => 'Box personnalisée ajoutée au panier ! 🎉']);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * Modifie la quantité d'un élément
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
    public function retirer(string $type, int $id): Response
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
     * Vide le panier
     */
    #[Route('/vider', name: 'app_panier_vider', methods: ['POST'])]
    public function vider(): Response
    {
        try {
            $this->panierService->viderPanier();
            $this->addFlash('success', 'Panier vidé avec succès.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue.');
        }

        return $this->redirectToRoute('app_panier_index');
    }

    // ========== MÉTHODES PRIVÉES ==========

    /**
     * Calcule le total du panier session
     */
    private function calculerTotal(array $panier): float
    {
        $total = 0;
        
        foreach ($panier['produits'] ?? [] as $item) {
            $total += $item['produit']->getPrix() * $item['quantite'];
        }
        
        foreach ($panier['boxes'] ?? [] as $item) {
            $total += $item['box']->getPrix() * $item['quantite'];
        }
        
        foreach ($panier['boxes_perso'] ?? [] as $item) {
            $total += $item['box']->getPrix();
        }
        
        return $total;
    }

    /**
     * Calcule le nombre d'articles du panier session
     */
    private function calculerNombreArticles(array $panier): int
    {
        $nombre = 0;
        
        foreach ($panier['produits'] ?? [] as $item) {
            $nombre += $item['quantite'];
        }
        
        foreach ($panier['boxes'] ?? [] as $item) {
            $nombre += $item['quantite'];
        }
        
        $nombre += count($panier['boxes_perso'] ?? []);
        
        return $nombre;
    }

    /**
     * Formate le panier BDD pour l'API
     */
    private function formatPanierBDD(\App\Entity\User $user): array
    {
        $panierEntity = $this->panierRepository->findByUser($user);
        
        if (!$panierEntity || $panierEntity->isEmpty()) {
            return [
                'success' => true,
                'items' => [],
                'total' => 0,
                'nombre_articles' => 0,
                'is_empty' => true
            ];
        }
        
        $items = [];
        foreach ($panierEntity->getLignesPanier() as $ligne) {
            $item = [
                'id' => $ligne->getId(),
                'nom' => $ligne->getNomArticle(),
                'quantite' => $ligne->getQuantite(),
                'prix_unitaire' => $ligne->getPrixUnitaire(),
                'sous_total' => $ligne->getSousTotal(),
                'image' => null,
                'type' => 'Cookie'
            ];
            
            if ($ligne->getProduit()) {
                $item['image'] = $ligne->getProduit()->getImage();
            }
            
            if ($ligne->getBox()) {
                $item['image'] = $ligne->getBox()->getImage();
                $item['type'] = $ligne->isBoxPersonnalisable() ? 'Box Personnalisée' : 'Box ' . ucfirst($ligne->getBox()->getType());
                
                if ($ligne->isBoxPersonnalisable()) {
                    $composition = [];
                    foreach ($ligne->getCompositionsPanier() as $compo) {
                        $composition[] = [
                            'nom' => $compo->getProduit()->getName(),
                            'quantite' => $compo->getQuantite()
                        ];
                    }
                    $item['composition'] = $composition;
                }
            }
            
            $items[] = $item;
        }
        
        return [
            'success' => true,
            'items' => $items,
            'total' => $panierEntity->getTotal(),
            'nombre_articles' => $panierEntity->getNombreArticles(),
            'is_empty' => false
        ];
    }

    /**
     * Formate le panier session pour l'API
     */
    private function formatPanierSession(array $panier): array
    {
        $items = [];
        
        foreach ($panier['produits'] ?? [] as $item) {
            $items[] = [
                'nom' => $item['produit']->getName(),
                'quantite' => $item['quantite'],
                'prix_unitaire' => $item['produit']->getPrix(),
                'sous_total' => $item['produit']->getPrix() * $item['quantite'],
                'image' => $item['produit']->getImage(),
                'type' => 'Cookie'
            ];
        }
        
        foreach ($panier['boxes'] ?? [] as $item) {
            $items[] = [
                'nom' => $item['box']->getNom(),
                'quantite' => $item['quantite'],
                'prix_unitaire' => $item['box']->getPrix(),
                'sous_total' => $item['box']->getPrix() * $item['quantite'],
                'image' => $item['box']->getImage(),
                'type' => 'Box ' . ucfirst($item['box']->getType())
            ];
        }
        
        foreach ($panier['boxes_perso'] ?? [] as $item) {
            $composition = [];
            foreach ($item['composition'] as $produitId => $qty) {
                $produit = $this->entityManager->getRepository(Produit::class)->find($produitId);
                if ($produit) {
                    $composition[] = ['nom' => $produit->getName(), 'quantite' => $qty];
                }
            }
            
            $items[] = [
                'nom' => 'Box Personnalisée',
                'quantite' => 1,
                'prix_unitaire' => $item['box']->getPrix(),
                'sous_total' => $item['box']->getPrix(),
                'image' => $item['box']->getImage(),
                'type' => 'Box Personnalisée',
                'composition' => $composition
            ];
        }
        
        return [
            'success' => true,
            'items' => $items,
            'total' => $this->calculerTotal($panier),
            'nombre_articles' => $this->calculerNombreArticles($panier),
            'is_empty' => empty($items)
        ];
    }

    /**
     * Redirige vers la page précédente ou l'accueil
     */
    private function redirectToReferer(Request $request): Response
    {
        $referer = $request->headers->get('referer');
        return $referer ? $this->redirect($referer) : $this->redirectToRoute('app_home');
    }
}