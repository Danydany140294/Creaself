<?php

namespace App\Controller;

use App\Repository\CommandeRepository;
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
        $this->stripeSecretKey = $_ENV['STRIPE_SECRET_KEY'] ?? '';
        \Stripe\Stripe::setApiKey($this->stripeSecretKey);
    }

    #[Route('/paiement', name: 'app_paiement')]
    public function index(
        PanierRepository $panierRepo,
        LignePanierRepository $lignePanierRepo
    ): Response
    {
        $session = $this->container->get('request_stack')->getSession();
        $user = $this->getUser();
        $panierData = [
            'lignes' => [],
            'total' => 0,
            'nombre_articles' => 0,
            'is_empty' => true
        ];

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
        } else {
            $panierSession = $session->get('panier', [
                'produits' => [],
                'boxes' => [],
                'boxes_perso' => []
            ]);

            $total = 0;
            $nbArticles = 0;

            foreach ($panierSession['produits'] ?? [] as $item) {
                $total += $item['produit']->getPrix() * $item['quantite'];
                $nbArticles += $item['quantite'];
            }

            foreach ($panierSession['boxes'] ?? [] as $item) {
                $total += $item['box']->getPrix() * $item['quantite'];
                $nbArticles += $item['quantite'];
            }

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

        if ($panierData['is_empty']) {
            $this->addFlash('warning', 'Votre panier est vide !');
            return $this->redirectToRoute('app_panier_index');
        }

        $fraisLivraison = $panierData['total'] >= 50 ? 0 : 4.50;
        $totalFinal = $panierData['total'] + $fraisLivraison;
        $stripePublicKey = $_ENV['STRIPE_PUBLIC_KEY'] ?? '';

        return $this->render('Page/paiement.html.twig', [
            'show_success_modal' => false,
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

            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => (int)($amount * 100),
                'currency' => 'eur',
                'automatic_payment_methods' => ['enabled' => true],
                'description' => 'Commande CreaSelf Cookies',
            ]);

            return new JsonResponse(['clientSecret' => $paymentIntent->client_secret]);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
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

            if ($user) {
                $panier = $panierRepo->findOneBy(['user' => $user]);

                if (!$panier) {
                    return new JsonResponse(['error' => 'Panier introuvable'], 404);
                }

                $lignes = $lignePanierRepo->findBy(['panier' => $panier]);

                if (empty($lignes)) {
                    return new JsonResponse(['error' => 'Votre panier est vide'], 400);
                }

                foreach ($lignes as $ligne) {
                    $lineItems[] = [
                        'price_data' => [
                            'currency' => 'eur',
                            'product_data' => ['name' => $ligne->getNomArticle()],
                            'unit_amount' => (int)($ligne->getPrixUnitaire() * 100),
                        ],
                        'quantity' => $ligne->getQuantite(),
                    ];
                }
            } else {
                $session = $this->container->get('request_stack')->getSession();
                $panierSession = $session->get('panier', []);

                if (empty($panierSession['produits']) && empty($panierSession['boxes']) && empty($panierSession['boxes_perso'])) {
                    return new JsonResponse(['error' => 'Votre panier est vide'], 400);
                }

                foreach ($panierSession['produits'] ?? [] as $item) {
                    $lineItems[] = [
                        'price_data' => [
                            'currency' => 'eur',
                            'product_data' => ['name' => $item['produit']->getName()],
                            'unit_amount' => (int)($item['produit']->getPrix() * 100),
                        ],
                        'quantity' => $item['quantite'],
                    ];
                }

                foreach ($panierSession['boxes'] ?? [] as $item) {
                    $lineItems[] = [
                        'price_data' => [
                            'currency' => 'eur',
                            'product_data' => ['name' => $item['box']->getNom()],
                            'unit_amount' => (int)($item['box']->getPrix() * 100),
                        ],
                        'quantity' => $item['quantite'],
                    ];
                }

                foreach ($panierSession['boxes_perso'] ?? [] as $item) {
                    $lineItems[] = [
                        'price_data' => [
                            'currency' => 'eur',
                            'product_data' => ['name' => 'Box Personnalisée'],
                            'unit_amount' => (int)($item['box']->getPrix() * 100),
                        ],
                        'quantity' => 1,
                    ];
                }
            }

            if (empty($lineItems)) {
                return new JsonResponse(['error' => 'Aucun article à commander'], 400);
            }

            $checkoutSession = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => $lineItems,
                'mode' => 'payment',
                'success_url' => $this->generateUrl('app_paiement_success_stripe', [], UrlGeneratorInterface::ABSOLUTE_URL) . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => $this->generateUrl('app_panier_index', [], UrlGeneratorInterface::ABSOLUTE_URL),
                'customer_email' => $user ? $user->getEmail() : null,
            ]);

            return new JsonResponse(['sessionId' => $checkoutSession->id]);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    // ✅ successStripe — affichage uniquement, la commande est créée par le webhook
    #[Route('/paiement/success-stripe', name: 'app_paiement_success_stripe')]
    public function successStripe(
        Request $request,
        CommandeRepository $commandeRepo
    ): Response
    {
        $sessionId = $request->query->get('session_id');

        if (!$sessionId) {
            $this->addFlash('error', 'Session de paiement invalide');
            return $this->redirectToRoute('app_home');
        }

        // Attendre max 3 secondes que le webhook crée la commande
        $commande = null;
        for ($i = 0; $i < 3; $i++) {
            $commande = $commandeRepo->findOneBy(['stripeSessionId' => $sessionId]);
            if ($commande) break;
            sleep(1);
        }

        $session = $this->container->get('request_stack')->getSession();

        if ($commande) {
            $session->set('commande_success', [
                'numero' => $commande->getNumeroCommande(),
                'total' => $commande->getTotalTTC(),
                'date' => $commande->getDateCommande(),
                'email' => $commande->getUser()?->getEmail()
            ]);
        }

        $user = $this->getUser();
        return $this->redirectToRoute($user ? 'app_user_dashboard' : 'app_home');
    }

    #[Route('/paiement/success/{id}', name: 'app_paiement_success')]
    public function success(int $id, EntityManagerInterface $em): Response
    {
        $commande = $em->getRepository(\App\Entity\Commande::class)->find($id);

        if (!$commande) {
            $this->addFlash('error', 'Commande introuvable');
            return $this->redirectToRoute('app_home');
        }

        $session = $this->container->get('request_stack')->getSession();
        $session->set('commande_success', [
            'numero' => $commande->getNumeroCommande(),
            'total' => $commande->getTotalTTC(),
            'date' => $commande->getDateCommande(),
            'email' => $commande->getUser() ? $commande->getUser()->getEmail() : null
        ]);

        return $this->redirectToRoute('app_paiement');
    }

    #[Route('/clear-commande-session', name: 'app_clear_commande_session', methods: ['POST'])]
    public function clearCommandeSession(): JsonResponse
    {
        $session = $this->container->get('request_stack')->getSession();
        $session->remove('commande_success');
        return new JsonResponse(['success' => true]);
    }

    #[Route('/paiement/cancel', name: 'app_paiement_cancel')]
    public function cancel(): Response
    {
        $this->addFlash('warning', 'Le paiement a été annulé');
        return $this->redirectToRoute('app_panier_index');
    }
}