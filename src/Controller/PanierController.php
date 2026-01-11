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
        
        // Si c'est un panier BDD (utilisateur connecté)
        if (isset($panier['lignes'])) {
            return $this->render('Page/panier.html.twig', [
                'panier' => $panier,
            ]);
        }
        
        // Si c'est un panier session (visiteur) - calculer le total
        $total = 0;
        $nombreArticles = 0;
        
        // Calculer le total des produits
        if (isset($panier['produits'])) {
            foreach ($panier['produits'] as $item) {
                $total += $item['produit']->getPrix() * $item['quantite'];
                $nombreArticles += $item['quantite'];
            }
        }
        
        // Calculer le total des boxes
        if (isset($panier['boxes'])) {
            foreach ($panier['boxes'] as $item) {
                $total += $item['box']->getPrix() * $item['quantite'];
                $nombreArticles += $item['quantite'];
            }
        }
        
        // Calculer le total des boxes perso
        if (isset($panier['boxes_perso'])) {
            foreach ($panier['boxes_perso'] as $item) {
                $total += $item['box']->getPrix();
                $nombreArticles += 1;
            }
        }
        
        // Ajouter les données calculées au panier
        $panier['total'] = $total;
        $panier['nombre_articles'] = $nombreArticles;
        $panier['is_empty'] = $nombreArticles === 0;
        
        return $this->render('Page/panier.html.twig', [
            'panier' => $panier,
        ]);
    }

    /**
     * Retourne le panier au format JSON pour la modal
     */
    #[Route('/api/panier', name: 'app_panier_api', methods: ['GET'])]
    public function getPanierApi(): Response
    {
        try {
            $panier = $this->panierService->getPanier();
            
            // Si c'est un panier BDD (utilisateur connecté)
            if (isset($panier['lignes'])) {
                $items = $this->formatPanierBdd($panier['lignes']);
                
                return $this->json([
                    'success' => true,
                    'items' => $items,
                    'total' => $panier['total'],
                    'nombre_articles' => $panier['nombre_articles'],
                    'is_empty' => $panier['is_empty']
                ]);
            }
            
            // Si c'est un panier session (visiteur)
            $result = $this->formatPanierSession($panier);
            
            return $this->json([
                'success' => true,
                'items' => $result['items'],
                'total' => $result['total'],
                'nombre_articles' => $result['nombre_articles'],
                'is_empty' => empty($result['items'])
            ]);
            
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
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
    #[Route('/ajouter-box-personnalisable', name: 'app_panier_ajouter_box_personnalisable', methods: ['POST'])]
    public function ajouterBoxPersonnalisable(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        
        if (!$data || !isset($data['cookies'])) {
            return $this->json([
                'success' => false,
                'message' => 'Données invalides'
            ], 400);
        }

        $cookiesChoisis = $data['cookies'];
        
        if (empty($cookiesChoisis)) {
            return $this->json([
                'success' => false,
                'message' => 'Veuillez sélectionner au moins un cookie.'
            ], 400);
        }

        // Convertir les clés "produit_X" en IDs numériques
        $cookiesIds = [];
        foreach ($cookiesChoisis as $key => $quantite) {
            $produitId = (int) str_replace('produit_', '', $key);
            $cookiesIds[$produitId] = (int) $quantite;
        }

        try {
            // Récupérer la box "template" personnalisable depuis la BDD
            $boxTemplate = $this->entityManager->getRepository(Box::class)
                ->findOneBy(['type' => 'personnalisable']);
            
            if (!$boxTemplate) {
                return $this->json([
                    'success' => false,
                    'message' => 'Box personnalisable non disponible'
                ], 404);
            }

            $this->panierService->ajouterBoxPersonnalisable($boxTemplate, $cookiesIds);
            
            return $this->json([
                'success' => true,
                'message' => 'Box personnalisée ajoutée au panier ! 🎉'
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
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
     * Formate les lignes d'un panier BDD pour l'API
     */
    private function formatPanierBdd(array $lignes): array
    {
        $items = [];
        
        foreach ($lignes as $ligne) {
            $item = [
                'id' => $ligne['id'],
                'nom' => $ligne['nom_article'],
                'quantite' => $ligne['quantite'],
                'prix_unitaire' => $ligne['prix_unitaire'],
                'sous_total' => $ligne['sous_total'],
                'image' => null,
                'type' => 'Cookie'
            ];
            
            // Produit simple
            if (isset($ligne['produit'])) {
                $item['image'] = $ligne['produit']['image'];
                $item['type'] = 'Cookie';
            }
            
            // Box fixe
            if (isset($ligne['box']) && !$ligne['is_box_perso']) {
                $item['image'] = $ligne['box']['image'];
                $item['type'] = 'Box ' . ucfirst($ligne['box']['type']);
            }
            
            // Box personnalisable
            if ($ligne['is_box_perso']) {
                $item['image'] = $ligne['box']['image'] ?? 'box-perso.jpg';
                $item['type'] = 'Box Personnalisée';
                
                // Ajouter la composition
                if (isset($ligne['compositions'])) {
                    $item['composition'] = [];
                    foreach ($ligne['compositions'] as $compo) {
                        $item['composition'][] = [
                            'nom' => $compo['produit']['name'],
                            'quantite' => $compo['quantite']
                        ];
                    }
                }
            }
            
            $items[] = $item;
        }
        
        return $items;
    }

    /**
     * Formate un panier session pour l'API
     */
    private function formatPanierSession(array $panier): array
    {
        $items = [];
        $total = 0;
        $nombreArticles = 0;
        
        // Produits
        if (isset($panier['produits'])) {
            foreach ($panier['produits'] as $item) {
                $produit = $item['produit'];
                $quantite = $item['quantite'];
                
                $items[] = [
                    'nom' => $produit->getName(),
                    'quantite' => $quantite,
                    'prix_unitaire' => $produit->getPrix(),
                    'sous_total' => $produit->getPrix() * $quantite,
                    'image' => $produit->getImage(),
                    'type' => 'Cookie'
                ];
                
                $total += $produit->getPrix() * $quantite;
                $nombreArticles += $quantite;
            }
        }
        
        // Boxes fixes
        if (isset($panier['boxes'])) {
            foreach ($panier['boxes'] as $item) {
                $box = $item['box'];
                $quantite = $item['quantite'];
                
                $items[] = [
                    'nom' => $box->getNom(),
                    'quantite' => $quantite,
                    'prix_unitaire' => $box->getPrix(),
                    'sous_total' => $box->getPrix() * $quantite,
                    'image' => $box->getImage(),
                    'type' => 'Box ' . ucfirst($box->getType())
                ];
                
                $total += $box->getPrix() * $quantite;
                $nombreArticles += $quantite;
            }
        }
        
        // Boxes perso
        if (isset($panier['boxes_perso'])) {
            foreach ($panier['boxes_perso'] as $item) {
                $box = $item['box'];
                
                $composition = [];
                foreach ($item['composition'] as $produitId => $qty) {
                    $produit = $this->entityManager->getRepository(Produit::class)->find($produitId);
                    if ($produit) {
                        $composition[] = [
                            'nom' => $produit->getName(),
                            'quantite' => $qty
                        ];
                    }
                }
                
                $items[] = [
                    'nom' => 'Box Personnalisée',
                    'quantite' => 1,
                    'prix_unitaire' => $box->getPrix(),
                    'sous_total' => $box->getPrix(),
                    'image' => $box->getImage(),
                    'type' => 'Box Personnalisée',
                    'composition' => $composition
                ];
                
                $total += $box->getPrix();
                $nombreArticles += 1;
            }
        }
        
        return [
            'items' => $items,
            'total' => $total,
            'nombre_articles' => $nombreArticles
        ];
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