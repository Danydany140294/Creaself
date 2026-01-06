<?php

namespace App\Service;

use App\Entity\Box;
use App\Entity\Commande;
use App\Entity\CompositionBoxPersonnalisable;
use App\Entity\LigneCommande;
use App\Entity\Produit;
use App\Entity\User;
use App\Enum\CommandeStatut;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;

class CommandeService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CommandeRepository $commandeRepository
    ) {}

    /**
     * Crée une nouvelle commande
     */
    public function creerCommande(User $user): Commande
    {
        $commande = new Commande();
        $commande->setUser($user);
        $commande->setNumeroCommande($this->genererNumeroCommande());
        $commande->setStatut(CommandeStatut::EN_ATTENTE);
        $commande->setDateCommande(new \DateTime());
        $commande->setTotalTTC(0);

        return $commande;
    }

    /**
     * Ajoute un produit à une commande
     */
    public function ajouterProduit(Commande $commande, Produit $produit, int $quantite): void
    {
        if ($produit->getStock() < $quantite) {
            throw new \Exception("Stock insuffisant pour le produit {$produit->getName()}");
        }

        if (!$produit->isDisponible()) {
            throw new \Exception("Le produit {$produit->getName()} n'est pas disponible");
        }

        $ligneCommande = new LigneCommande();
        $ligneCommande->setCommande($commande);
        $ligneCommande->setProduit($produit);
        $ligneCommande->setQuantite($quantite);
        $ligneCommande->setPrixUnitaire($produit->getPrix());

        $commande->addLigneCommande($ligneCommande);
    }

    /**
     * Ajoute une box à une commande
     */
    public function ajouterBox(Commande $commande, Box $box, int $quantite): void
    {
        if ($box->getStock() < $quantite) {
            throw new \Exception("Stock insuffisant pour la box {$box->getNom()}");
        }

        $ligneCommande = new LigneCommande();
        $ligneCommande->setCommande($commande);
        $ligneCommande->setBox($box);
        $ligneCommande->setQuantite($quantite);
        $ligneCommande->setPrixUnitaire($box->getPrix());

        $commande->addLigneCommande($ligneCommande);
    }

    /**
     * Ajoute une box personnalisable avec ses cookies choisis
     * 
     * @param Commande $commande
     * @param Box $boxPersonnalisable
     * @param array $cookiesChoisis ['produit' => Produit, 'quantite' => int]
     * @param int $quantiteBox Nombre de box personnalisables (généralement 1)
     */
    public function ajouterBoxPersonnalisable(Commande $commande, Box $boxPersonnalisable, array $cookiesChoisis, int $quantiteBox = 1): void
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
            $quantite = $cookie['quantite'] * $quantiteBox; // Si on commande plusieurs box perso

            if ($produit->getStock() < $quantite) {
                throw new \Exception("Stock insuffisant pour {$produit->getName()}");
            }

            if (!$produit->isDisponible()) {
                throw new \Exception("Le produit {$produit->getName()} n'est pas disponible");
            }
        }

        // Créer la ligne de commande pour la box
        $ligneCommande = new LigneCommande();
        $ligneCommande->setCommande($commande);
        $ligneCommande->setBox($boxPersonnalisable);
        $ligneCommande->setQuantite($quantiteBox);
        $ligneCommande->setPrixUnitaire($boxPersonnalisable->getPrix());

        // Ajouter les compositions (les cookies choisis)
        foreach ($cookiesChoisis as $cookie) {
            $composition = new CompositionBoxPersonnalisable();
            $composition->setLigneCommande($ligneCommande);
            $composition->setProduit($cookie['produit']);
            $composition->setQuantite($cookie['quantite']);

            $ligneCommande->addCompositionBox($composition);
        }

        $commande->addLigneCommande($ligneCommande);
    }

    /**
     * Valide et enregistre une commande
     */
    public function validerCommande(Commande $commande): void
    {
        if ($commande->getLignesCommande()->isEmpty()) {
            throw new \Exception("La commande doit contenir au moins un article");
        }

        // Vérifier et mettre à jour les stocks
        foreach ($commande->getLignesCommande() as $ligne) {
            if ($ligne->getProduit()) {
                $produit = $ligne->getProduit();
                if ($produit->getStock() < $ligne->getQuantite()) {
                    throw new \Exception("Stock insuffisant pour {$produit->getName()}");
                }
                $produit->setStock($produit->getStock() - $ligne->getQuantite());
            }

            if ($ligne->getBox()) {
                $box = $ligne->getBox();
                
                // Si c'est une box personnalisable, décrémenter le stock de chaque cookie
                if ($ligne->isBoxPersonnalisable()) {
                    foreach ($ligne->getCompositionsBox() as $composition) {
                        $produit = $composition->getProduit();
                        $quantiteARetirer = $composition->getQuantite() * $ligne->getQuantite();
                        
                        if ($produit->getStock() < $quantiteARetirer) {
                            throw new \Exception("Stock insuffisant pour {$produit->getName()}");
                        }
                        $produit->setStock($produit->getStock() - $quantiteARetirer);
                    }
                }
                
                if ($box->getStock() < $ligne->getQuantite()) {
                    throw new \Exception("Stock insuffisant pour {$box->getNom()}");
                }
                $box->setStock($box->getStock() - $ligne->getQuantite());
            }
        }

        // Calculer le total
        $commande->setTotalTTC($commande->calculerTotal());
        $commande->setStatut(CommandeStatut::CONFIRMEE);

        $this->entityManager->persist($commande);
        $this->entityManager->flush();
    }

    /**
     * Annule une commande et remet le stock
     */
    public function annulerCommande(Commande $commande): void
    {
        if ($commande->getStatut() === CommandeStatut::LIVREE) {
            throw new \Exception("Impossible d'annuler une commande déjà livrée");
        }

        if ($commande->getStatut() === CommandeStatut::ANNULEE) {
            throw new \Exception("Cette commande est déjà annulée");
        }

        // Remettre le stock si la commande était confirmée
        if ($commande->getStatut() !== CommandeStatut::EN_ATTENTE) {
            foreach ($commande->getLignesCommande() as $ligne) {
                if ($ligne->getProduit()) {
                    $produit = $ligne->getProduit();
                    $produit->setStock($produit->getStock() + $ligne->getQuantite());
                }

                if ($ligne->getBox()) {
                    $box = $ligne->getBox();
                    
                    // Si c'est une box personnalisable, remettre le stock de chaque cookie
                    if ($ligne->isBoxPersonnalisable()) {
                        foreach ($ligne->getCompositionsBox() as $composition) {
                            $produit = $composition->getProduit();
                            $quantiteARestituer = $composition->getQuantite() * $ligne->getQuantite();
                            $produit->setStock($produit->getStock() + $quantiteARestituer);
                        }
                    }
                    
                    $box->setStock($box->getStock() + $ligne->getQuantite());
                }
            }
        }

        $commande->setStatut(CommandeStatut::ANNULEE);
        $this->entityManager->flush();
    }

    /**
     * Change le statut d'une commande
     */
    public function changerStatut(Commande $commande, CommandeStatut $nouveauStatut): void
    {
        if ($commande->getStatut() === CommandeStatut::ANNULEE) {
            throw new \Exception("Impossible de modifier une commande annulée");
        }

        $commande->setStatut($nouveauStatut);
        $this->entityManager->flush();
    }

    /**
     * Génère un numéro de commande unique
     */
    private function genererNumeroCommande(): string
    {
        return 'CMD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    }

    /**
     * Récupère les commandes d'un utilisateur
     */
    public function getCommandesUtilisateur(User $user): array
    {
        return $this->commandeRepository->findByUser($user);
    }

    /**
     * Récupère une commande par son numéro
     */
    public function getCommandeParNumero(string $numeroCommande): ?Commande
    {
        return $this->commandeRepository->findByNumeroCommande($numeroCommande);
    }
}