<?php

namespace App\Service;

use App\Entity\Box;
use App\Entity\CompositionPanierPersonnalisable;
use App\Entity\LignePanier;
use App\Entity\Panier;
use App\Entity\Produit;
use App\Entity\User;
use App\Repository\PanierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

class PanierService
{
    private const SESSION_KEY = 'panier';

    public function __construct(
        private EntityManagerInterface $entityManager,
        private PanierRepository $panierRepository,
        private RequestStack $requestStack,
        private Security $security
    ) {}

    /**
     * Récupère le panier (session ou BDD selon si connecté)
     */
    public function getPanier(): array
    {
        $user = $this->security->getUser();

        if ($user instanceof User) {
            // Utilisateur connecté : panier en BDD
            return $this->getPanierBDD($user);
        } else {
            // Visiteur : panier en session
            return $this->getPanierSession();
        }
    }

    /**
     * Panier en session (format tableau)
     */
    private function getPanierSession(): array
    {
        $session = $this->requestStack->getSession();
        $panier = $session->get(self::SESSION_KEY, [
            'produits' => [],
            'boxes' => [],
            'boxes_perso' => [],
            'date_expiration' => new \DateTime('+10 minutes')
        ]);

        // Vérifier l'expiration
        if (isset($panier['date_expiration']) && $panier['date_expiration'] < new \DateTime()) {
            $this->viderPanierSession();
            return [
                'produits' => [],
                'boxes' => [],
                'boxes_perso' => [],
                'date_expiration' => new \DateTime('+10 minutes')
            ];
        }

        return $panier;
    }

    /**
     * Panier en BDD (format entité)
     */
    private function getPanierBDD(User $user): array
    {
        $panier = $this->panierRepository->findByUser($user);

        if (!$panier) {
            $panier = new Panier();
            $panier->setUser($user);
            $this->entityManager->persist($panier);
            $this->entityManager->flush();
        } elseif ($panier->isExpire()) {
            $this->viderPanierBDD($panier);
        } else {
            $panier->rafraichirExpiration();
            $this->entityManager->flush();
        }

        // Formater les lignes pour éviter les références circulaires
        $lignesFormatees = [];
        foreach ($panier->getLignesPanier() as $ligne) {
            $ligneData = [
                'id' => $ligne->getId(),
                'quantite' => $ligne->getQuantite(),
                'sous_total' => $ligne->getSousTotal(),
                'prix_unitaire' => $ligne->getPrixUnitaire(),
                'nom_article' => $ligne->getNomArticle(),
                'is_box_perso' => $ligne->isBoxPersonnalisable()
            ];

            if ($ligne->getProduit()) {
                $ligneData['produit'] = [
                    'id' => $ligne->getProduit()->getId(),
                    'name' => $ligne->getProduit()->getName(),
                    'prix' => $ligne->getProduit()->getPrix(),
                    'image' => $ligne->getProduit()->getImage(),
                    'stock' => $ligne->getProduit()->getStock()
                ];
            }

            if ($ligne->getBox()) {
                $ligneData['box'] = [
                    'id' => $ligne->getBox()->getId(),
                    'nom' => $ligne->getBox()->getNom(),
                    'prix' => $ligne->getBox()->getPrix(),
                    'image' => $ligne->getBox()->getImage(),
                    'stock' => $ligne->getBox()->getStock(),
                    'type' => $ligne->getBox()->getType()
                ];
            }

            if ($ligne->isBoxPersonnalisable()) {
                $compositions = [];
                foreach ($ligne->getCompositionsPanier() as $compo) {
                    $compositions[] = [
                        'produit' => [
                            'id' => $compo->getProduit()->getId(),
                            'name' => $compo->getProduit()->getName(),
                            'prix' => $compo->getProduit()->getPrix()
                        ],
                        'quantite' => $compo->getQuantite()
                    ];
                }
                $ligneData['compositions'] = $compositions;
            }

            $lignesFormatees[] = $ligneData;
        }

        return [
            'panier_id' => $panier->getId(),
            'user_id' => $panier->getUser()->getId(),
            'date_creation' => $panier->getDateCreation(),
            'date_expiration' => $panier->getDateExpiration(),
            'lignes' => $lignesFormatees,
            'total' => $panier->getTotal(),
            'nombre_articles' => $panier->getNombreArticles(),
            'is_empty' => $panier->isEmpty()
        ];
    }

    /**
     * Ajoute un produit au panier
     */
    public function ajouterProduit(Produit $produit, int $quantite = 1): void
    {
        if ($quantite <= 0) {
            throw new \Exception("La quantité doit être supérieure à 0");
        }

        if ($produit->getStock() < $quantite) {
            throw new \Exception("Stock insuffisant pour {$produit->getName()}");
        }

        if (!$produit->isDisponible()) {
            throw new \Exception("Le produit {$produit->getName()} n'est pas disponible");
        }

        $user = $this->security->getUser();

        if ($user instanceof User) {
            $this->ajouterProduitBDD($user, $produit, $quantite);
        } else {
            $this->ajouterProduitSession($produit, $quantite);
        }
    }

    /**
     * Ajoute un produit en session
     */
    private function ajouterProduitSession(Produit $produit, int $quantite): void
    {
        $session = $this->requestStack->getSession();
        $panier = $this->getPanierSession();

        $produitId = $produit->getId();

        if (isset($panier['produits'][$produitId])) {
            $nouvelleQuantite = $panier['produits'][$produitId]['quantite'] + $quantite;
            
            if ($produit->getStock() < $nouvelleQuantite) {
                throw new \Exception("Stock insuffisant pour {$produit->getName()}");
            }
            
            $panier['produits'][$produitId]['quantite'] = $nouvelleQuantite;
        } else {
            $panier['produits'][$produitId] = [
                'produit' => $produit,
                'quantite' => $quantite
            ];
        }

        $panier['date_expiration'] = new \DateTime('+10 minutes');
        $session->set(self::SESSION_KEY, $panier);
    }

    /**
     * Ajoute un produit en BDD
     */
    private function ajouterProduitBDD(User $user, Produit $produit, int $quantite): void
    {
        $panierEntity = $this->panierRepository->findByUser($user);
        
        if (!$panierEntity) {
            $panierEntity = new Panier();
            $panierEntity->setUser($user);
            $this->entityManager->persist($panierEntity);
        }

        // Vérifier si le produit existe déjà
        foreach ($panierEntity->getLignesPanier() as $ligne) {
            if ($ligne->getProduit() && $ligne->getProduit()->getId() === $produit->getId()) {
                $nouvelleQuantite = $ligne->getQuantite() + $quantite;
                if ($produit->getStock() < $nouvelleQuantite) {
                    throw new \Exception("Stock insuffisant pour {$produit->getName()}");
                }
                $ligne->setQuantite($nouvelleQuantite);
                $panierEntity->rafraichirExpiration();
                $this->entityManager->flush();
                return;
            }
        }

        // Créer une nouvelle ligne
        $lignePanier = new LignePanier();
        $lignePanier->setPanier($panierEntity);
        $lignePanier->setProduit($produit);
        $lignePanier->setQuantite($quantite);

        $panierEntity->addLignePanier($lignePanier);
        $panierEntity->rafraichirExpiration();
        
        $this->entityManager->persist($lignePanier);
        $this->entityManager->flush();
    }

    /**
     * Ajoute une box au panier
     */
    public function ajouterBox(Box $box, int $quantite = 1): void
    {
        if ($quantite <= 0) {
            throw new \Exception("La quantité doit être supérieure à 0");
        }

        if ($box->getType() === 'personnalisable') {
            throw new \Exception("Utilisez ajouterBoxPersonnalisable() pour les box personnalisables");
        }

        if ($box->getStock() < $quantite) {
            throw new \Exception("Stock insuffisant pour {$box->getNom()}");
        }

        $user = $this->security->getUser();

        if ($user instanceof User) {
            $this->ajouterBoxBDD($user, $box, $quantite);
        } else {
            $this->ajouterBoxSession($box, $quantite);
        }
    }

    /**
     * Ajoute une box en session
     */
    private function ajouterBoxSession(Box $box, int $quantite): void
    {
        $session = $this->requestStack->getSession();
        $panier = $this->getPanierSession();

        $boxId = $box->getId();

        if (isset($panier['boxes'][$boxId])) {
            $nouvelleQuantite = $panier['boxes'][$boxId]['quantite'] + $quantite;
            
            if ($box->getStock() < $nouvelleQuantite) {
                throw new \Exception("Stock insuffisant pour {$box->getNom()}");
            }
            
            $panier['boxes'][$boxId]['quantite'] = $nouvelleQuantite;
        } else {
            $panier['boxes'][$boxId] = [
                'box' => $box,
                'quantite' => $quantite
            ];
        }

        $panier['date_expiration'] = new \DateTime('+10 minutes');
        $session->set(self::SESSION_KEY, $panier);
    }

    /**
     * Ajoute une box en BDD
     */
    private function ajouterBoxBDD(User $user, Box $box, int $quantite): void
    {
        $panierEntity = $this->panierRepository->findByUser($user);
        
        if (!$panierEntity) {
            $panierEntity = new Panier();
            $panierEntity->setUser($user);
            $this->entityManager->persist($panierEntity);
        }

        // Vérifier si la box existe déjà
        foreach ($panierEntity->getLignesPanier() as $ligne) {
            if ($ligne->getBox() && $ligne->getBox()->getId() === $box->getId() && !$ligne->isBoxPersonnalisable()) {
                $nouvelleQuantite = $ligne->getQuantite() + $quantite;
                if ($box->getStock() < $nouvelleQuantite) {
                    throw new \Exception("Stock insuffisant pour {$box->getNom()}");
                }
                $ligne->setQuantite($nouvelleQuantite);
                $panierEntity->rafraichirExpiration();
                $this->entityManager->flush();
                return;
            }
        }

        // Créer une nouvelle ligne
        $lignePanier = new LignePanier();
        $lignePanier->setPanier($panierEntity);
        $lignePanier->setBox($box);
        $lignePanier->setQuantite($quantite);

        $panierEntity->addLignePanier($lignePanier);
        $panierEntity->rafraichirExpiration();
        
        $this->entityManager->persist($lignePanier);
        $this->entityManager->flush();
    }

    /**
     * Modifie la quantité d'un élément du panier
     */
    public function modifierQuantite(string $type, int $id, int $nouvelleQuantite): void
    {
        if ($nouvelleQuantite <= 0) {
            $this->retirerElement($type, $id);
            return;
        }

        $user = $this->security->getUser();

        if ($user instanceof User) {
            $this->modifierQuantiteBDD($id, $nouvelleQuantite);
        } else {
            $this->modifierQuantiteSession($type, $id, $nouvelleQuantite);
        }
    }

    /**
     * Modifie la quantité en session
     */
    private function modifierQuantiteSession(string $type, int $id, int $nouvelleQuantite): void
    {
        $session = $this->requestStack->getSession();
        $panier = $this->getPanierSession();

        if ($type === 'produit' && isset($panier['produits'][$id])) {
            $produit = $panier['produits'][$id]['produit'];
            if ($produit->getStock() < $nouvelleQuantite) {
                throw new \Exception("Stock insuffisant");
            }
            $panier['produits'][$id]['quantite'] = $nouvelleQuantite;
        } elseif ($type === 'box' && isset($panier['boxes'][$id])) {
            $box = $panier['boxes'][$id]['box'];
            if ($box->getStock() < $nouvelleQuantite) {
                throw new \Exception("Stock insuffisant");
            }
            $panier['boxes'][$id]['quantite'] = $nouvelleQuantite;
        }

        $panier['date_expiration'] = new \DateTime('+7 days');
        $session->set(self::SESSION_KEY, $panier);
    }

    /**
     * Modifie la quantité en BDD
     */
    private function modifierQuantiteBDD(int $ligneId, int $nouvelleQuantite): void
    {
        $ligne = $this->entityManager->getRepository(LignePanier::class)->find($ligneId);
        
        if (!$ligne) {
            throw new \Exception("Ligne de panier introuvable");
        }

        if ($ligne->getProduit()) {
            if ($ligne->getProduit()->getStock() < $nouvelleQuantite) {
                throw new \Exception("Stock insuffisant");
            }
        } elseif ($ligne->getBox() && !$ligne->isBoxPersonnalisable()) {
            if ($ligne->getBox()->getStock() < $nouvelleQuantite) {
                throw new \Exception("Stock insuffisant");
            }
        } elseif ($ligne->isBoxPersonnalisable()) {
            throw new \Exception("Impossible de modifier la quantité d'une box personnalisable");
        }

        $ligne->setQuantite($nouvelleQuantite);
        $ligne->getPanier()->rafraichirExpiration();
        
        $this->entityManager->flush();
    }

    /**
     * Retire un élément du panier
     */
    public function retirerElement(string $type, int $id): void
    {
        $user = $this->security->getUser();

        if ($user instanceof User) {
            $this->retirerElementBDD($id);
        } else {
            $this->retirerElementSession($type, $id);
        }
    }

    /**
     * Retire un élément de la session
     */
    private function retirerElementSession(string $type, int $id): void
    {
        $session = $this->requestStack->getSession();
        $panier = $this->getPanierSession();

        if ($type === 'produit') {
            unset($panier['produits'][$id]);
        } elseif ($type === 'box') {
            unset($panier['boxes'][$id]);
        } elseif ($type === 'box_perso') {
            unset($panier['boxes_perso'][$id]);
        }

        $session->set(self::SESSION_KEY, $panier);
    }

    /**
     * Retire une ligne de la BDD
     */
    private function retirerElementBDD(int $ligneId): void
    {
        $ligne = $this->entityManager->getRepository(LignePanier::class)->find($ligneId);
        
        if (!$ligne) {
            throw new \Exception("Ligne de panier introuvable");
        }

        $panier = $ligne->getPanier();
        $panier->removeLignePanier($ligne);
        $panier->rafraichirExpiration();
        
        $this->entityManager->remove($ligne);
        $this->entityManager->flush();
    }

    /**
     * Vide le panier
     */
    public function viderPanier(): void
    {
        $user = $this->security->getUser();

        if ($user instanceof User) {
            $panierEntity = $this->panierRepository->findByUser($user);
            if ($panierEntity) {
                $this->viderPanierBDD($panierEntity);
            }
        } else {
            $this->viderPanierSession();
        }
    }

    /**
     * Vide le panier en session
     */
    private function viderPanierSession(): void
    {
        $session = $this->requestStack->getSession();
        $session->remove(self::SESSION_KEY);
    }

    /**
     * Vide le panier en BDD
     */
    private function viderPanierBDD(Panier $panier): void
    {
        foreach ($panier->getLignesPanier() as $ligne) {
            $this->entityManager->remove($ligne);
        }
        
        $panier->getLignesPanier()->clear();
        $panier->rafraichirExpiration();
        
        $this->entityManager->flush();
    }

    /**
     * Migre le panier session vers la BDD lors de la connexion
     */
    public function migrerSessionVersBDD(User $user): void
    {
        // Récupérer directement depuis RequestStack
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return;
        }
        
        $session = $request->getSession();
        $panierSessionData = $session->get(self::SESSION_KEY, null);
        
        if (!$panierSessionData || (empty($panierSessionData['produits']) && empty($panierSessionData['boxes']))) {
            return; // Rien à migrer
        }

        // Récupérer ou créer le panier BDD
        $panierBDD = $this->panierRepository->findByUser($user);
        if (!$panierBDD) {
            $panierBDD = new Panier();
            $panierBDD->setUser($user);
            $this->entityManager->persist($panierBDD);
        }

        // Migrer les produits
        foreach ($panierSessionData['produits'] as $item) {
            try {
                $produitId = $item['produit']->getId();
                $quantite = $item['quantite'];
                
                // ← FIX : Récupérer le produit depuis la BDD (entité managée)
                $produit = $this->entityManager->getRepository(Produit::class)->find($produitId);
                if (!$produit) {
                    continue; // Produit introuvable, passer au suivant
                }
                
                // Vérifier si le produit existe déjà dans le panier BDD
                $ligneExistante = null;
                foreach ($panierBDD->getLignesPanier() as $ligne) {
                    if ($ligne->getProduit() && $ligne->getProduit()->getId() === $produit->getId()) {
                        $ligneExistante = $ligne;
                        break;
                    }
                }
                
                if ($ligneExistante) {
                    // Additionner les quantités
                    $ligneExistante->setQuantite($ligneExistante->getQuantite() + $quantite);
                } else {
                    // Créer nouvelle ligne
                    $lignePanier = new LignePanier();
                    $lignePanier->setPanier($panierBDD);
                    $lignePanier->setProduit($produit);
                    $lignePanier->setQuantite($quantite);
                    $panierBDD->addLignePanier($lignePanier);
                    $this->entityManager->persist($lignePanier);
                }
            } catch (\Exception $e) {
                // Ignorer les erreurs, continuer la migration
                continue;
            }
        }

        // Migrer les boxes
        foreach ($panierSessionData['boxes'] as $item) {
            try {
                $boxId = $item['box']->getId();
                $quantite = $item['quantite'];
                
                // ← FIX : Récupérer la box depuis la BDD (entité managée)
                $box = $this->entityManager->getRepository(Box::class)->find($boxId);
                if (!$box) {
                    continue; // Box introuvable, passer à la suivante
                }
                
                // Vérifier si la box existe déjà dans le panier BDD
                $ligneExistante = null;
                foreach ($panierBDD->getLignesPanier() as $ligne) {
                    if ($ligne->getBox() && $ligne->getBox()->getId() === $box->getId() && !$ligne->isBoxPersonnalisable()) {
                        $ligneExistante = $ligne;
                        break;
                    }
                }
                
                if ($ligneExistante) {
                    // Additionner les quantités
                    $ligneExistante->setQuantite($ligneExistante->getQuantite() + $quantite);
                } else {
                    // Créer nouvelle ligne
                    $lignePanier = new LignePanier();
                    $lignePanier->setPanier($panierBDD);
                    $lignePanier->setBox($box);
                    $lignePanier->setQuantite($quantite);
                    $panierBDD->addLignePanier($lignePanier);
                    $this->entityManager->persist($lignePanier);
                }
            } catch (\Exception $e) {
                // Ignorer les erreurs, continuer la migration
                continue;
            }
        }

        $panierBDD->rafraichirExpiration();
        $this->entityManager->flush();

        // Vider la session après migration réussie
        $session->remove(self::SESSION_KEY);
    }

    /**
     * Nettoie les paniers expirés
     */
    public function nettoyerPaniersExpires(): int
    {
        return $this->panierRepository->deleteExpired();
    }
}