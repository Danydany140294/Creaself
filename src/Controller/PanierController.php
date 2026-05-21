<?php

namespace App\Controller;

use App\Entity\Box;
use App\Entity\Produit;
use App\Entity\User;
use App\Repository\BoxRepository;
use App\Repository\PanierRepository;
use App\Repository\ProduitRepository;
use App\Service\PanierService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/panier')]
class PanierController extends AbstractController
{
    public function __construct(
        private readonly PanierService $panierService,
        private readonly EntityManagerInterface $entityManager,
        private readonly PanierRepository $panierRepository,
        private readonly Security $security
    ) {}

    // ======================================================
    // AFFICHAGE DU PANIER
    // ======================================================

    #[Route('', name: 'app_panier_index', methods: ['GET'])]
    public function index(): Response
    {
        $user = $this->security->getUser();

        if ($user instanceof User) {
            $panier = $this->formatPanierBDD($user);
        } else {
            $panier = $this->formatPanierSession(
                $this->panierService->getPanier()
            );
        }

        return $this->render('Page/panier.html.twig', [
            'panier'            => $panier,
            'stripe_public_key' => $this->getParameter('stripe_public_key'),
        ]);
    }

    #[Route('/api', name: 'app_panier_api', methods: ['GET'])]
    public function getPanierApi(): JsonResponse
    {
        try {
            $user = $this->security->getUser();

            if ($user instanceof User) {
                return $this->json($this->formatPanierBDD($user));
            }

            return $this->json(
                $this->formatPanierSession(
                    $this->panierService->getPanier()
                )
            );
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    #[Route('/vider', name: 'app_panier_vider', methods: ['POST'])]
public function viderPanier(Request $request): Response
{
    $user = $this->security->getUser();

    if ($user instanceof \App\Entity\User) {
        $panier = $this->panierRepository->findByUser($user);
        if ($panier) {
            foreach ($panier->getLignesPanier() as $ligne) {
                $this->entityManager->remove($ligne);
            }
            $this->entityManager->flush();
        }
    } else {
        $request->getSession()->remove('panier');
    }

    $this->addFlash('success', 'Panier vidé !');
    return $this->redirectToRoute('app_panier_index');
}

    // ======================================================
    // AJOUT PRODUITS / BOXES FIXES
    // ======================================================

    #[Route('/ajouter-produit/{id}', name: 'app_panier_ajouter_produit', methods: ['POST'])]
    public function ajouterProduit(Produit $produit, Request $request): Response
    {
        $quantite = max(1, (int) $request->request->get('quantite', 1));

        try {
            $this->panierService->ajouterProduit($produit, $quantite);
            $this->addFlash('success', sprintf('"%s" ajouté au panier !', $produit->getName()));
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToReferer($request);
    }

    #[Route('/ajouter-box/{id}', name: 'app_panier_ajouter_box', methods: ['POST'])]
    public function ajouterBox(Box $box, Request $request): Response
    {
        $quantite = max(1, (int) $request->request->get('quantite', 1));

        try {
            $this->panierService->ajouterBox($box, $quantite);
            $this->addFlash('success', sprintf('"%s" ajoutée au panier !', $box->getNom()));
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToReferer($request);
    }

    

    // ======================================================
    // BOX PERSONNALISÉE — ROUTE PRINCIPALE (box.js)
    // Reçoit : { cookies: {...}, taille: 6|12|24 }
    // ======================================================

    #[Route('/ajouter-box-personnalisable', name: 'app_panier_ajouter_box_personnalisable', methods: ['POST'])]
    public function ajouterBoxPersonnalisable(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data || empty($data['cookies'])) {
            return $this->json([
                'success' => false,
                'message' => 'Aucun cookie sélectionné',
            ], 400);
        }

        // Taille envoyée par box.js (6, 12 ou 24) — défaut 12 pour compatibilité
        $taille = isset($data['taille']) ? (int) $data['taille'] : 12;

        if (!in_array($taille, [6, 12, 24], true)) {
            return $this->json([
                'success' => false,
                'message' => 'Taille invalide (6, 12 ou 24 uniquement)',
            ], 400);
        }

        // Convertir les clés "produit_X" en IDs numériques
        $cookiesIds = [];
        foreach ($data['cookies'] as $key => $quantite) {
            $produitId             = (int) str_replace('produit_', '', $key);
            $cookiesIds[$produitId] = (int) $quantite;
        }

        try {
            $boxTemplate = $this->entityManager
                ->getRepository(Box::class)
                ->findOneBy(['type' => 'personnalisable']);

            if (!$boxTemplate) {
                return $this->json([
                    'success' => false,
                    'message' => 'Box personnalisable introuvable en base',
                ], 404);
            }

            $this->panierService->ajouterBoxPersonnalisable(
                $boxTemplate,
                $cookiesIds,
                $taille
            );

            return $this->json([
                'success' => true,
                'message' => "Box de {$taille} cookies ajoutée au panier ! 🎉",
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    // ======================================================
    // UPDATE QUANTITÉ
    // ======================================================

    #[Route('/update-quantity', name: 'app_panier_update_quantity', methods: ['POST'])]
    public function updateQuantity(Request $request): JsonResponse
    {
        try {
            $data   = json_decode($request->getContent(), true);
            $id     = $data['id']     ?? null;
            $type   = $data['type']   ?? null;
            $change = (int) ($data['change'] ?? 0);

            if (!$id || !$type) {
                return $this->json([
                    'success' => false,
                    'message' => 'Données invalides',
                ], 400);
            }

            $user = $this->security->getUser();

            // Utilisateur connecté
            if ($user instanceof User) {
                $panier = $this->panierRepository->findByUser($user);

                if (!$panier) {
                    return $this->json([
                        'success' => false,
                        'message' => 'Panier introuvable',
                    ], 404);
                }

                foreach ($panier->getLignesPanier() as $ligne) {
                    if ($ligne->getId() == $id) {
                        $nouvelleQuantite = $ligne->getQuantite() + $change;

                        if ($nouvelleQuantite <= 0) {
                            $panier->removeLignePanier($ligne);
                            $this->entityManager->remove($ligne);
                        } else {
                            $ligne->setQuantite($nouvelleQuantite);
                        }

                        $this->entityManager->flush();

                        return $this->json([
                            'success' => true,
                            'message' => 'Quantité mise à jour',
                        ]);
                    }
                }

                return $this->json([
                    'success' => false,
                    'message' => 'Article introuvable',
                ], 404);
            }

            // Session
            $panier    = $this->panierService->getPanier();
            $itemFound = false;

            foreach ($panier['produits'] ?? [] as $key => $item) {
                if ($item['produit']->getId() == $id && $type === 'Cookie') {
                    $panier['produits'][$key]['quantite'] += $change;

                    if ($panier['produits'][$key]['quantite'] <= 0) {
                        unset($panier['produits'][$key]);
                    }

                    $itemFound = true;
                }
            }

            foreach ($panier['boxes'] ?? [] as $key => $item) {
                if ($item['box']->getId() == $id && str_starts_with($type, 'Box')) {
                    $panier['boxes'][$key]['quantite'] += $change;

                    if ($panier['boxes'][$key]['quantite'] <= 0) {
                        unset($panier['boxes'][$key]);
                    }

                    $itemFound = true;
                }
            }

            $request->getSession()->set('panier', $panier);

            return $this->json([
                'success' => $itemFound,
                'message' => $itemFound ? 'Quantité mise à jour' : 'Article introuvable',
            ], $itemFound ? 200 : 404);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // ======================================================
    // SUPPRESSION D'ARTICLE
    // ======================================================

    #[Route('/remove', name: 'app_panier_remove', methods: ['POST'])]
    public function remove(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $id   = $data['id']   ?? null;
            $type = $data['type'] ?? null;

            if (!$id || !$type) {
                return $this->json([
                    'success' => false,
                    'message' => 'Données invalides',
                ], 400);
            }

            $user = $this->security->getUser();

            // Utilisateur connecté
            if ($user instanceof User) {
                $panier = $this->panierRepository->findByUser($user);

                if (!$panier) {
                    return $this->json([
                        'success' => false,
                        'message' => 'Panier introuvable',
                    ], 404);
                }

                foreach ($panier->getLignesPanier() as $ligne) {
                    if ($ligne->getId() == $id) {
                        $panier->removeLignePanier($ligne);
                        $this->entityManager->remove($ligne);
                        $this->entityManager->flush();

                        return $this->json([
                            'success' => true,
                            'message' => 'Article supprimé',
                        ]);
                    }
                }

                return $this->json([
                    'success' => false,
                    'message' => 'Article introuvable',
                ], 404);
            }

            // Session
            $panier    = $this->panierService->getPanier();
            $itemFound = false;

            $panier['produits'] = array_filter(
                $panier['produits'] ?? [],
                function ($item) use ($id, $type, &$itemFound) {
                    $remove = ($item['produit']->getId() == $id && $type === 'Cookie');
                    if ($remove) $itemFound = true;
                    return !$remove;
                }
            );

            $panier['boxes'] = array_filter(
                $panier['boxes'] ?? [],
                function ($item) use ($id, $type, &$itemFound) {
                    $remove = ($item['box']->getId() == $id && str_starts_with($type, 'Box'));
                    if ($remove) $itemFound = true;
                    return !$remove;
                }
            );

            if (!$itemFound && $type === 'Box Personnalisée' && isset($panier['boxes_perso'][$id])) {
                unset($panier['boxes_perso'][$id]);
                $itemFound = true;
            }

            if (!$itemFound) {
                return $this->json([
                    'success' => false,
                    'message' => 'Article introuvable',
                ], 404);
            }

            $request->getSession()->set('panier', $panier);

            return $this->json([
                'success' => true,
                'message' => 'Article supprimé',
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // ======================================================
    // FORMATAGE SESSION
    // ======================================================

    private function formatPanierSession(array $panier): array
    {
        $items = [];

        foreach ($panier['produits'] ?? [] as $item) {
            $items[] = [
                'id'           => $item['produit']->getId(),
                'nom'          => $item['produit']->getName(),
                'quantite'     => $item['quantite'],
                'prix_unitaire'=> $item['produit']->getPrix(),
                'sous_total'   => $item['produit']->getPrix() * $item['quantite'],
                'image'        => $item['produit']->getImage(),
                'type'         => 'Cookie',
            ];
        }

        foreach ($panier['boxes'] ?? [] as $item) {
            $items[] = [
                'id'           => $item['box']->getId(),
                'nom'          => $item['box']->getNom(),
                'quantite'     => $item['quantite'],
                'prix_unitaire'=> $item['box']->getPrix(),
                'sous_total'   => $item['box']->getPrix() * $item['quantite'],
                'image'        => $item['box']->getImage(),
                'type'         => 'Box ' . ucfirst($item['box']->getType()),
            ];
        }

        foreach ($panier['boxes_perso'] ?? [] as $index => $item) {
            $composition = [];

            foreach ($item['cookies'] as $produitId => $qty) {
                $produit = $this->entityManager
                    ->getRepository(Produit::class)
                    ->find($produitId);

                if ($produit) {
                    $composition[] = [
                        'nom'      => $produit->getName(),
                        'quantite' => $qty,
                    ];
                }
            }

            // Prix dynamique stocké en session (sinon fallback sur box)
            $prixUnitaire = $item['prix'] ?? $item['box']->getPrix();
            $taille       = $item['taille'] ?? null;

            $items[] = [
                'id'           => $index,
                'nom'          => $taille
                    ? "Box Personnalisée — {$taille} cookies"
                    : 'Box Personnalisée',
                'quantite'     => 1,
                'prix_unitaire'=> $prixUnitaire,
                'sous_total'   => $prixUnitaire,
                'image'        => $item['box']->getImage(),
                'type'         => 'Box Personnalisée',
                'composition'  => $composition,
                'taille'       => $taille,
            ];
        }

        return [
            'success'         => true,
            'items'           => $items,
            'total'           => $this->calculerTotal($panier),
            'nombre_articles' => $this->calculerNombreArticles($panier),
            'is_empty'        => empty($items),
        ];
    }

    // ======================================================
    // FORMATAGE BDD
    // ======================================================

    private function formatPanierBDD(User $user): array
    {
        $panier = $this->panierRepository->findByUser($user);

        if (!$panier || $panier->isEmpty()) {
            return [
                'success'         => true,
                'items'           => [],
                'total'           => 0,
                'nombre_articles' => 0,
                'is_empty'        => true,
            ];
        }

        $items = [];

        foreach ($panier->getLignesPanier() as $ligne) {
            $taille = $ligne->getTailleBox();

            $item = [
                'id'           => $ligne->getId(),
                'nom'          => $ligne->getNomArticle(),
                'quantite'     => $ligne->getQuantite(),
                'prix_unitaire'=> $ligne->getPrixUnitaire(),
                'sous_total'   => $ligne->getSousTotal(),
                'image'        => null,
                'type'         => 'Cookie',
                'taille'       => $taille,
            ];

            if ($ligne->getProduit()) {
                $item['image'] = $ligne->getProduit()->getImage();
            }

            if ($ligne->getBox()) {
                $item['image'] = $ligne->getBox()->getImage();

                if ($ligne->isBoxPersonnalisable()) {
                    $item['type'] = $taille
                        ? "Box Personnalisée — {$taille} cookies"
                        : 'Box Personnalisée';
                } else {
                    $item['type'] = 'Box ' . ucfirst($ligne->getBox()->getType());
                }
            }

            $items[] = $item;
        }

        return [
            'success'         => true,
            'items'           => $items,
            'total'           => $panier->getTotal(),
            'nombre_articles' => $panier->getNombreArticles(),
            'is_empty'        => false,
        ];
    }

    // ======================================================
    // CALCULS SESSION
    // ======================================================

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
            // Utiliser le prix dynamique stocké en session
            $total += $item['prix'] ?? $item['box']->getPrix();
        }

        return $total;
    }

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

    // ======================================================
    // UTILITAIRE
    // ======================================================

    private function redirectToReferer(Request $request): Response
    {
        $referer = $request->headers->get('referer');

        return $referer
            ? $this->redirect($referer)
            : $this->redirectToRoute('app_home');
    }
}
