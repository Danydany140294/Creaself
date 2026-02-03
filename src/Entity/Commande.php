<?php

namespace App\Entity;

use App\Enum\CommandeStatut;
use App\Repository\CommandeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommandeRepository::class)]
class Commande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $numeroCommande = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $dateCommande = null;

    #[ORM\Column(length: 50, enumType: CommandeStatut::class)]
    private ?CommandeStatut $statut = null;

    #[ORM\Column]
    private ?float $totalTTC = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $dateLivraison = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $dateExpedition = null;

    #[ORM\ManyToOne(inversedBy: 'commandes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    /**
     * @var Collection<int, LigneCommande>
     */
    #[ORM\OneToMany(targetEntity: LigneCommande::class, mappedBy: 'commande', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $lignesCommande;

    #[ORM\ManyToOne(targetEntity: Adresse::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Adresse $adresseLivraison = null;

    public function __construct()
    {
        $this->lignesCommande = new ArrayCollection();
        $this->dateCommande = new \DateTime();
        $this->statut = CommandeStatut::EN_ATTENTE;
    }

    // ========================================
    // GETTERS & SETTERS BASIQUES
    // ========================================

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumeroCommande(): ?string
    {
        return $this->numeroCommande;
    }

    public function setNumeroCommande(string $numeroCommande): static
    {
        $this->numeroCommande = $numeroCommande;
        return $this;
    }

    public function getDateCommande(): ?\DateTimeInterface
    {
        return $this->dateCommande;
    }

    public function setDateCommande(\DateTimeInterface $dateCommande): static
    {
        $this->dateCommande = $dateCommande;
        return $this;
    }

    public function getStatut(): ?CommandeStatut
    {
        return $this->statut;
    }

    public function setStatut(CommandeStatut $statut): static
    {
        $this->statut = $statut;
        return $this;
    }

    public function getTotalTTC(): ?float
    {
        return $this->totalTTC;
    }

    public function setTotalTTC(float $totalTTC): static
    {
        $this->totalTTC = $totalTTC;
        return $this;
    }

    /**
     * Alias pour compatibilité
     */
    public function getMontantTotal(): ?float
    {
        return $this->totalTTC;
    }

    public function getDateLivraison(): ?\DateTimeInterface
    {
        return $this->dateLivraison;
    }

    public function setDateLivraison(?\DateTimeInterface $dateLivraison): static
    {
        $this->dateLivraison = $dateLivraison;
        return $this;
    }

    public function getDateExpedition(): ?\DateTimeInterface
    {
        return $this->dateExpedition;
    }

    public function setDateExpedition(?\DateTimeInterface $dateExpedition): static
    {
        $this->dateExpedition = $dateExpedition;
        return $this;
    }

    // ========================================
    // RELATIONS
    // ========================================

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return Collection<int, LigneCommande>
     */
    public function getLignesCommande(): Collection
    {
        return $this->lignesCommande;
    }

    public function addLigneCommande(LigneCommande $ligneCommande): static
    {
        if (!$this->lignesCommande->contains($ligneCommande)) {
            $this->lignesCommande->add($ligneCommande);
            $ligneCommande->setCommande($this);
        }
        return $this;
    }

    public function removeLigneCommande(LigneCommande $ligneCommande): static
    {
        if ($this->lignesCommande->removeElement($ligneCommande)) {
            if ($ligneCommande->getCommande() === $this) {
                $ligneCommande->setCommande(null);
            }
        }
        return $this;
    }

    public function getAdresseLivraison(): ?Adresse
    {
        return $this->adresseLivraison;
    }

    public function setAdresseLivraison(?Adresse $adresseLivraison): static
    {
        $this->adresseLivraison = $adresseLivraison;
        return $this;
    }

    // ========================================
    // MÉTHODES MÉTIER
    // ========================================

    /**
     * Calcule le total TTC de la commande à partir des lignes
     */
    public function calculerTotal(): float
    {
        $total = 0;
        foreach ($this->lignesCommande as $ligne) {
            $total += $ligne->getSousTotal();
        }
        return $total;
    }

    /**
     * Compte le nombre total d'articles dans la commande
     */
    public function getNombreTotalArticles(): int
    {
        $total = 0;
        foreach ($this->lignesCommande as $ligne) {
            $total += $ligne->getQuantite();
        }
        return $total;
    }

    /**
     * Retourne le nombre de lignes différentes dans la commande
     */
    public function getNombreLignes(): int
    {
        return $this->lignesCommande->count();
    }

    /**
     * Retourne les premières images de produits (pour l'affichage)
     * 
     * @param int $limit Nombre maximum d'images à retourner
     * @return array<string>
     */
    public function getImagesPreview(int $limit = 3): array
    {
        $images = [];
        $count = 0;
        
        foreach ($this->lignesCommande as $ligne) {
            if ($count >= $limit) {
                break;
            }
            
            $produit = $ligne->getProduit();
            if ($produit && $produit->getImage()) {
                $images[] = $produit->getImage();
                $count++;
            }
        }
        
        return $images;
    }

    /**
     * Retourne le nombre d'images restantes (pour afficher "+X")
     */
    public function getNombreImagesRestantes(int $displayed = 3): int
    {
        $total = 0;
        
        foreach ($this->lignesCommande as $ligne) {
            if ($ligne->getProduit() && $ligne->getProduit()->getImage()) {
                $total++;
            }
        }
        
        return max(0, $total - $displayed);
    }

    /**
     * Vérifie si la commande est en cours (ni livrée, ni annulée)
     */
    public function isEnCours(): bool
    {
        return !in_array($this->statut, [
            CommandeStatut::LIVREE,
            CommandeStatut::ANNULEE
        ]);
    }

    /**
     * Vérifie si la commande peut être annulée
     */
    public function isAnnulable(): bool
    {
        return in_array($this->statut, [
            CommandeStatut::EN_ATTENTE,
            CommandeStatut::EN_PREPARATION
        ]);
    }

    /**
     * Retourne un libellé lisible du statut
     */
    public function getStatutLabel(): string
    {
        return match($this->statut) {
            CommandeStatut::EN_ATTENTE => 'En attente',
            CommandeStatut::EN_PREPARATION => 'En préparation',
            CommandeStatut::EXPEDIEE => 'Expédiée',
            CommandeStatut::LIVREE => 'Livrée',
            CommandeStatut::ANNULEE => 'Annulée',
            default => 'Inconnu',
        };
    }

    /**
     * Retourne la classe CSS du badge selon le statut
     */
    public function getStatutBadgeClass(): string
    {
        return match($this->statut) {
            CommandeStatut::EN_ATTENTE => 'badge-warning',
            CommandeStatut::EN_PREPARATION => 'badge-info',
            CommandeStatut::EXPEDIEE => 'badge-primary',
            CommandeStatut::LIVREE => 'badge-success',
            CommandeStatut::ANNULEE => 'badge-danger',
            default => 'badge-secondary',
        };
    }

    /**
     * Retourne un nom descriptif pour la commande
     */
    public function getNomCommande(): string
    {
        $nombreProduits = $this->getNombreLignes();
        
        if ($nombreProduits === 0) {
            return 'Commande vide';
        }
        
        if ($nombreProduits === 1) {
            $ligne = $this->lignesCommande->first();
            $produit = $ligne->getProduit();
            return $produit ? $produit->getName() : 'Produit inconnu';
        }
        
        // Construire un nom à partir des premiers produits
        $noms = [];
        $count = 0;
        foreach ($this->lignesCommande as $ligne) {
            if ($count >= 2) {
                break;
            }
            $produit = $ligne->getProduit();
            if ($produit) {
                $noms[] = $produit->getName();
                $count++;
            }
        }
        
        $nom = implode(' & ', $noms);
        
        if ($nombreProduits > 2) {
            $nom .= ' (+' . ($nombreProduits - 2) . ')';
        }
        
        return $nom;
    }

    /**
     * Retourne la date de livraison prévue ou estimée
     */
    public function getDateLivraisonPrevue(): ?\DateTimeInterface
    {
        // Si date de livraison définie, la retourner
        if ($this->dateLivraison) {
            return $this->dateLivraison;
        }
        
        // Sinon, estimer selon le statut
        if ($this->dateExpedition) {
            // Livraison prévue 3 jours après expédition
            return (clone $this->dateExpedition)->modify('+3 days');
        }
        
        if ($this->statut === CommandeStatut::EN_PREPARATION) {
            // Livraison prévue 5 jours après la commande
            return (clone $this->dateCommande)->modify('+5 days');
        }
        
        return null;
    }

    /**
     * Retourne un message de livraison convivial
     */
    public function getMessageLivraison(): string
    {
        $datePrevue = $this->getDateLivraisonPrevue();
        
        if (!$datePrevue) {
            return 'Date de livraison à confirmer';
        }
        
        $maintenant = new \DateTime();
        $diff = $maintenant->diff($datePrevue);
        
        if ($diff->invert) {
            return 'Livraison en retard';
        }
        
        if ($diff->days === 0) {
            return 'Livraison prévue aujourd\'hui';
        }
        
        if ($diff->days === 1) {
            return 'Livraison prévue demain';
        }
        
        return sprintf('Livraison prévue dans %d jours', $diff->days);
    }

    /**
     * Pour l'affichage dans EasyAdmin et les formulaires
     */
    public function __toString(): string
    {
        return $this->numeroCommande ?? 'Commande #' . $this->id;
    }
}