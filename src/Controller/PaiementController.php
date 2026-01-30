<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\LigneCommande;
use App\Enum\CommandeStatut;
use App\Repository\PanierRepository;
use App\Repository\LignePanierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class PaiementController extends AbstractController
{
    private string $stripeSecretKey;
    
    public function __construct()
    {
        // Récupère la clé secrète depuis .env
        $this->stripeSecretKey = $_ENV['STRIPE_SECRET_KEY'] ?? '';
        \Stripe\Stripe::setApiKey($this->stripeSecretKey);
    }

    #[Route('/paiement', name: 'app_paiement')]
    public function index(
        PanierRepository $panierRepo,
        LignePanierRepository $lignePanierRepo
    ): Response
    {
        $user = $this->getUser();
        $panier = null;
        $panierData = [
            'lignes' => [],
            'total' => 0,
            'nombre_articles' => 0,
            'is_empty' => true
        ];

        // ========== UTILISATEUR CONNECTÉ ==========
        if ($user) {
            $panier = $panierRepo->findOneBy(['user' => $user]);
            
            if ($panier) {
                $lignes = $lignePanierRepo->findBy(['panier' => $panier]);
                $total = 0;
                $nbArticles = 0;

                foreach ($lignes as $ligne) {
                    $total += $ligne->getSousTotal();
                    $nbArticles += $ligne->getQuantite();
                }

                $panierData = [
                    'lignes' => $lignes,
                    'total' => $total,
                    'nombre_articles' => $nbArticles,
                    'is_empty' => empty($lignes)
                ];
            }
        } 
        // ========== VISITEUR (SESSION) ==========
        else {
            $session = $this->container->get('request_stack')->getSession();
            $panierSession = $session->get('panier', [
                'produits' => [],
                'boxes' => [],
                'boxes_perso' => []
            ]);

            $total = 0;
            $nbArticles = 0;

            // Compter produits
            foreach ($panierSession['produits'] ?? [] as $item) {
                $total += $item['produit']->getPrix() * $item['quantite'];
                $nbArticles += $item['quantite'];
            }

            // Compter boxes
            foreach ($panierSession['boxes'] ?? [] as $item) {
                $total += $item['box']->getPrix() * $item['quantite'];
                $nbArticles += $item['quantite'];
            }

            // Compter boxes perso
            foreach ($panierSession['boxes_perso'] ?? [] as $item) {
                $total += $item['box']->getPrix();
                $nbArticles += 1;
            }

            $panierData = [
                'produits' => $panierSession['produits'] ?? [],
                'boxes' => $panierSession['boxes'] ?? [],
                'boxes_perso' => $panierSession['boxes_perso'] ?? [],
                'total' => $total,
                'nombre_articles' => $nbArticles,
                'is_empty' => $total == 0
            ];
        }

        // Rediriger si panier vide
        if ($panierData['is_empty']) {
            $this->addFlash('warning', 'Votre panier est vide !');
            return $this->redirectToRoute('app_panier_index');
        }

        // Calculer les frais de livraison
        $fraisLivraison = $panierData['total'] >= 50 ? 0 : 4.50;
        $totalFinal = $panierData['total'] + $fraisLivraison;

        // Clé publique Stripe pour le frontend
        $stripePublicKey = $_ENV['STRIPE_PUBLIC_KEY'] ?? '';

        return $this->render('Page/paiement.html.twig', [
            'panier' => $panierData,
            'frais_livraison' => $fraisLivraison,
            'total_final' => $totalFinal,
            'stripe_public_key' => $stripePublicKey,
        ]);
    }

    #[Route('/paiement/create-payment-intent', name: 'app_paiement_payment_intent', methods: ['POST'])]
    public function createPaymentIntent(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $amount = $data['amount'] ?? 0;

            // Créer le PaymentIntent Stripe (montant en centimes)
            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => (int)($amount * 100), // Convertir en centimes
                'currency' => 'eur',
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
                'description' => 'Commande CreaSelf Cookies',
            ]);

            return new JsonResponse([
                'clientSecret' => $paymentIntent->client_secret
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/paiement/create-checkout-session', name: 'app_paiement_create_session', methods: ['POST'])]
    public function createCheckoutSession(
        Request $request,
        PanierRepository $panierRepo,
        LignePanierRepository $lignePanierRepo
    ): JsonResponse
    {
        try {
            $user = $this->getUser();
            $lineItems = [];

            // ========== UTILISATEUR CONNECTÉ ==========
            if ($user) {
                $panier = $panierRepo->findOneBy(['user' => $user]);
                
                if (!$panier) {
                    return new JsonResponse([
                        'error' => 'Panier introuvable'
                    ], 404);
                }
                
                $lignes = $lignePanierRepo->findBy(['panier' => $panier]);
                
                if (empty($lignes)) {
                    return new JsonResponse([
                        'error' => 'Votre panier est vide'
                    ], 400);
                }
                
                foreach ($lignes as $ligne) {
                    $lineItems[] = [
                        'price_data' => [
                            'currency' => 'eur',
                            'product_data' => [
                                'name' => $ligne->getNomArticle(),
                            ],
                            'unit_amount' => (int)($ligne->getPrixUnitaire() * 100), // En centimes
                        ],
                        'quantity' => $ligne->getQuantite(),
                    ];
                }
            } 
            // ========== VISITEUR (SESSION) ==========
            else {
                $session = $this->container->get('request_stack')->getSession();
                $panierSession = $session->get('panier', []);

                // Vérifier si le panier est vide
                if (empty($panierSession['produits']) && empty($panierSession['boxes']) && empty($panierSession['boxes_perso'])) {
                    return new JsonResponse([
                        'error' => 'Votre panier est vide'
                    ], 400);
                }

                // Ajouter les produits
                foreach ($panierSession['produits'] ?? [] as $item) {
                    $lineItems[] = [
                        'price_data' => [
                            'currency' => 'eur',
                            'product_data' => [
                                'name' => $item['produit']->getName(),
                            ],
                            'unit_amount' => (int)($item['produit']->getPrix() * 100),
                        ],
                        'quantity' => $item['quantite'],
                    ];
                }

                // Ajouter les boxes
                foreach ($panierSession['boxes'] ?? [] as $item) {
                    $lineItems[] = [
                        'price_data' => [
                            'currency' => 'eur',
                            'product_data' => [
                                'name' => $item['box']->getNom(),
                            ],
                            'unit_amount' => (int)($item['box']->getPrix() * 100),
                        ],
                        'quantity' => $item['quantite'],
                    ];
                }

                // Ajouter les boxes perso
                foreach ($panierSession['boxes_perso'] ?? [] as $item) {
                    $lineItems[] = [
                        'price_data' => [
                            'currency' => 'eur',
                            'product_data' => [
                                'name' => 'Box Personnalisée',
                            ],
                            'unit_amount' => (int)($item['box']->getPrix() * 100),
                        ],
                        'quantity' => 1,
                    ];
                }
            }

            // Vérifier qu'on a bien des articles
            if (empty($lineItems)) {
                return new JsonResponse([
                    'error' => 'Aucun article à commander'
                ], 400);
            }

            // Créer la session Stripe Checkout
            $checkoutSession = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => $lineItems,
                'mode' => 'payment',
                'success_url' => $this->generateUrl('app_paiement_success_stripe', [], UrlGeneratorInterface::ABSOLUTE_URL) . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => $this->generateUrl('app_panier_index', [], UrlGeneratorInterface::ABSOLUTE_URL),
                'customer_email' => $user ? $user->getEmail() : null,
            ]);

            return new JsonResponse([
                'sessionId' => $checkoutSession->id
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/paiement/success-stripe', name: 'app_paiement_success_stripe')]
    public function successStripe(
        Request $request,
        EntityManagerInterface $em,
        PanierRepository $panierRepo,
        LignePanierRepository $lignePanierRepo
    ): Response
    {
        $sessionId = $request->query->get('session_id');
        
        if (!$sessionId) {
            $this->addFlash('error', 'Session invalide');
            return $this->redirectToRoute('app_home');
        }

        try {
            // Récupérer la session Stripe pour vérifier le paiement
            $session = \Stripe\Checkout\Session::retrieve($sessionId);

            if ($session->payment_status !== 'paid') {
                $this->addFlash('error', 'Paiement non validé');
                return $this->redirectToRoute('app_panier_index');
            }

            $user = $this->getUser();
            
            // Créer la commande
            $commande = new Commande();
            $commande->setUser($user);
            
            // Générer un numéro de commande unique
            $numeroCommande = 'CMD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
            $commande->setNumeroCommande($numeroCommande);
            
            $commande->setDateCommande(new \DateTime());
            $commande->setStatut(CommandeStatut::PAYEE); // ✅ Maintenant disponible dans l'enum
            $commande->setTotalTTC($session->amount_total / 100); // Convertir centimes en euros
            
            $em->persist($commande);

            // Ajouter les lignes de commande depuis le panier
            if ($user) {
                $panier = $panierRepo->findOneBy(['user' => $user]);
                if ($panier) {
                    $lignesPanier = $lignePanierRepo->findBy(['panier' => $panier]);
                    
                    foreach ($lignesPanier as $lignePanier) {
                        $ligneCommande = new LigneCommande();
                        $ligneCommande->setCommande($commande);
                        
                        if ($lignePanier->getProduit()) {
                            $ligneCommande->setProduit($lignePanier->getProduit());
                        }
                        
                        if ($lignePanier->getBox()) {
                            $ligneCommande->setBox($lignePanier->getBox());
                        }
                        
                        $ligneCommande->setPrixUnitaire($lignePanier->getPrixUnitaire());
                        $ligneCommande->setQuantite($lignePanier->getQuantite());
                        
                        if ($lignePanier->isBoxPerso() && !$lignePanier->getCompositionsBox()->isEmpty()) {
                            foreach ($lignePanier->getCompositionsBox() as $compo) {
                                $ligneCommande->addCompositionBox($compo);
                            }
                        }
                        
                        $em->persist($ligneCommande);
                    }
                    
                    // Vider le panier
                    foreach ($lignesPanier as $ligne) {
                        $em->remove($ligne);
                    }
                    $em->remove($panier);
                }
            } else {
                // Vider le panier session pour visiteur
                $sessionCart = $this->container->get('request_stack')->getSession();
                $sessionCart->remove('panier');
            }

            $em->flush();

            // Afficher la page de succès
            return $this->render('paiement/success.html.twig', [
                'commande' => $commande
            ]);

        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur : ' . $e->getMessage());
            return $this->redirectToRoute('app_panier_index');
        }
    }

    #[Route('/paiement/success/{id}', name: 'app_paiement_success')]
    public function success(int $id, EntityManagerInterface $em): Response
    {
        $commande = $em->getRepository(Commande::class)->find($id);
        
        if (!$commande) {
            $this->addFlash('error', 'Commande introuvable');
            return $this->redirectToRoute('app_home');
        }

        return $this->render('paiement/success.html.twig', [
            'commande' => $commande
        ]);
    }

    #[Route('/paiement/cancel', name: 'app_paiement_cancel')]
    public function cancel(): Response
    {
        $this->addFlash('warning', 'Le paiement a été annulé');
        return $this->redirectToRoute('app_panier_index');
    }
}