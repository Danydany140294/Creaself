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

class PanierService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private PanierRepository $panierRepository
    ) {}

    /**
     * Récupère ou crée le panier d'un utilisateur
     */
    public function getPanierUtilisateur(User $user): Panier
    {
        $panier = $this->panierRepository->findByUser($user);

        if (!$panier) {
            $panier = new Panier();
            $panier->setUser($user);
            $this->entityManager->persist($panier);
            $this->entityManager->flush();
        } elseif ($panier->isExpire()) {
            // Si le panier est expiré, on le vide
            $this->viderPanier($panier);
        } else {
            // Rafraîchir l'expiration à chaque accès
            $panier->rafraichirExpiration();
            $this->entityManager->flush();
        }

        return $panier;
    }

    /**
     * Ajoute un produit au panier
     */
    public function ajouterProduit(Panier $panier, Produit $produit, int $quantite = 1): void
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

        // Vérifier si le produit existe déjà dans le panier
        foreach ($panier->getLignesPanier() as $ligne) {
            if ($ligne->getProduit() && $ligne->getProduit()->getId() === $produit->getId()) {
                $nouvelleQuantite = $ligne->getQuantite() + $quantite;
                if ($produit->getStock() < $nouvelleQuantite) {
                    throw new \Exception("Stock insuffisant pour {$produit->getName()}");
                }
                $ligne->setQuantite($nouvelleQuantite);
                $panier->rafraichirExpiration();
                $this->entityManager->flush();
                return;
            }
        }

        // Sinon, créer une nouvelle ligne
        $lignePanier = new LignePanier();
        $lignePanier->setPanier($panier);
        $lignePanier->setProduit($produit);
        $lignePanier->setQuantite($quantite);

        $panier->addLignePanier($lignePanier);
        $panier->rafraichirExpiration();
        
        $this->entityManager->persist($lignePanier);
        $this->entityManager->flush();
    }

    /**
     * Ajoute une box fixe au panier
     */
    public function ajouterBox(Panier $panier, Box $box, int $quantite = 1): void
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

        // Vérifier si la box existe déjà dans le panier
        foreach ($panier->getLignesPanier() as $ligne) {
            if ($ligne->getBox() && $ligne->getBox()->getId() === $box->getId() && !$ligne->isBoxPersonnalisable()) {
                $nouvelleQuantite = $ligne->getQuantite() + $quantite;
                if ($box->getStock() < $nouvelleQuantite) {
                    throw new \Exception("Stock insuffisant pour {$box->getNom()}");
                }
                $ligne->setQuantite($nouvelleQuantite);
                $panier->rafraichirExpiration();
                $this->entityManager->flush();
                return;
            }
        }

        // Sinon, créer une nouvelle ligne
        $lignePanier = new LignePanier();
        $lignePanier->setPanier($panier);
        $lignePanier->setBox($box);
        $lignePanier->setQuantite($quantite);

        $panier->addLignePanier($lignePanier);
        $panier->rafraichirExpiration();
        
        $this->entityManager->persist($lignePanier);
        $this->entityManager->flush();
    }

    /**
     * Ajoute une box personnalisable au panier
     * 
     * @param array $cookiesChoisis ['produit' => Produit, 'quantite' => int]
     */
    public function ajouterBoxPersonnalisable(Panier $panier, Box $boxPersonnalisable, array $cookiesChoisis): void
    {
        if ($boxPersonnalisable->getType() !== 'personnalisable') {
            throw new \Exception("Cette box n'est pas personnalisable");
        }

        // Vérifier qu'on a exactement 12 cookies au total
        $totalCookies = 0;
        foreach ($cookiesChoisis as $cookie) {
            $totalCookies += $cookie['quantite'];
        }

        if ($totalCookies !== 12) {
            throw new \Exception("Une box personnalisable doit contenir exactement 12 cookies (actuellement: {$totalCookies})");
        }

        // Vérifier le stock de chaque cookie
        foreach ($cookiesChoisis as $cookie) {
            $produit = $cookie['produit'];
            $quantite = $cookie['quantite'];

            if ($produit->getStock() < $quantite) {
                throw new \Exception("Stock insuffisant pour {$produit->getName()}");
            }

            if (!$produit->isDisponible()) {
                throw new \Exception("Le produit {$produit->getName()} n'est pas disponible");
            }
        }

        // Créer la ligne de panier pour la box
        $lignePanier = new LignePanier();
        $lignePanier->setPanier($panier);
        $lignePanier->setBox($boxPersonnalisable);
        $lignePanier->setQuantite(1);

        // Ajouter les compositions (les cookies choisis)
        foreach ($cookiesChoisis as $cookie) {
            $composition = new CompositionPanierPersonnalisable();
            $composition->setLignePanier($lignePanier);
            $composition->setProduit($cookie['produit']);
            $composition->setQuantite($cookie['quantite']);

            $lignePanier->addCompositionPanier($composition);
            $this->entityManager->persist($composition);
        }

        $panier->addLignePanier($lignePanier);
        $panier->rafraichirExpiration();
        
        $this->entityManager->persist($lignePanier);
        $this->entityManager->flush();
    }

    /**
     * Modifie la quantité d'une ligne du panier
     */
    public function modifierQuantite(LignePanier $ligne, int $nouvelleQuantite): void
    {
        if ($nouvelleQuantite <= 0) {
            $this->retirerLigne($ligne);
            return;
        }

        // Vérifier le stock disponible
        if ($ligne->getProduit()) {
            if ($ligne->getProduit()->getStock() < $nouvelleQuantite) {
                throw new \Exception("Stock insuffisant");
            }
        } elseif ($ligne->getBox() && !$ligne->isBoxPersonnalisable()) {
            if ($ligne->getBox()->getStock() < $nouvelleQuantite) {
                throw new \Exception("Stock insuffisant");
            }
        } elseif ($ligne->isBoxPersonnalisable()) {
            // Pour les box perso, on ne peut pas modifier la quantité facilement
            throw new \Exception("Impossible de modifier la quantité d'une box personnalisable. Veuillez la supprimer et en créer une nouvelle.");
        }

        $ligne->setQuantite($nouvelleQuantite);
        $ligne->getPanier()->rafraichirExpiration();
        
        $this->entityManager->flush();
    }

    /**
     * Retire une ligne du panier
     */
    public function retirerLigne(LignePanier $ligne): void
    {
        $panier = $ligne->getPanier();
        $panier->removeLignePanier($ligne);
        $panier->rafraichirExpiration();
        
        $this->entityManager->remove($ligne);
        $this->entityManager->flush();
    }

    /**
     * Vide complètement le panier
     */
    public function viderPanier(Panier $panier): void
    {
        foreach ($panier->getLignesPanier() as $ligne) {
            $this->entityManager->remove($ligne);
        }
        
        $panier->getLignesPanier()->clear();
        $panier->rafraichirExpiration();
        
        $this->entityManager->flush();
    }

    /**
     * Nettoie les paniers expirés
     */
    public function nettoyerPaniersExpires(): int
    {
        return $this->panierRepository->deleteExpired();
    }
}