<?php

namespace App\Entity;

use App\Repository\LignePanierRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LignePanierRepository::class)]
class LignePanier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'lignesPanier')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Panier $panier = null;

    #[ORM\ManyToOne]
    private ?Produit $produit = null;

    #[ORM\ManyToOne]
    private ?Box $box = null;

    #[ORM\Column]
    private ?int $quantite = null;

    /**
     * @var Collection<int, CompositionPanierPersonnalisable>
     */
    #[ORM\OneToMany(targetEntity: CompositionPanierPersonnalisable::class, mappedBy: 'lignePanier', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $compositionsPanier;

    public function __construct()
    {
        $this->compositionsPanier = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPanier(): ?Panier
    {
        return $this->panier;
    }

    public function setPanier(?Panier $panier): static
    {
        $this->panier = $panier;

        return $this;
    }

    public function getProduit(): ?Produit
    {
        return $this->produit;
    }

    public function setProduit(?Produit $produit): static
    {
        $this->produit = $produit;

        return $this;
    }

    public function getBox(): ?Box
    {
        return $this->box;
    }

    public function setBox(?Box $box): static
    {
        $this->box = $box;

        return $this;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): static
    {
        $this->quantite = $quantite;

        return $this;
    }

    /**
     * @return Collection<int, CompositionPanierPersonnalisable>
     */
    public function getCompositionsPanier(): Collection
    {
        return $this->compositionsPanier;
    }

    public function addCompositionPanier(CompositionPanierPersonnalisable $compositionPanier): static
    {
        if (!$this->compositionsPanier->contains($compositionPanier)) {
            $this->compositionsPanier->add($compositionPanier);
            $compositionPanier->setLignePanier($this);
        }

        return $this;
    }

    public function removeCompositionPanier(CompositionPanierPersonnalisable $compositionPanier): static
    {
        if ($this->compositionsPanier->removeElement($compositionPanier)) {
            if ($compositionPanier->getLignePanier() === $this) {
                $compositionPanier->setLignePanier(null);
            }
        }

        return $this;
    }

    /**
     * Calcule le sous-total de cette ligne
     */
    public function getSousTotal(): float
    {
        if ($this->produit) {
            return $this->quantite * $this->produit->getPrix();
        }
        
        if ($this->box) {
            return $this->quantite * $this->box->getPrix();
        }
        
        return 0;
    }

    /**
     * Vérifie si cette ligne est une box personnalisable
     */
    public function isBoxPersonnalisable(): bool
    {
        return $this->box !== null && 
               $this->box->getType() === 'personnalisable' && 
               !$this->compositionsPanier->isEmpty();
    }

    /**
     * Récupère le nom de l'article
     */
    public function getNomArticle(): string
    {
        if ($this->produit) {
            return $this->produit->getName();
        }
        
        if ($this->box) {
            return $this->box->getNom();
        }
        
        return 'Article inconnu';
    }

    /**
     * Récupère le prix unitaire
     */
    public function getPrixUnitaire(): float
    {
        if ($this->produit) {
            return $this->produit->getPrix();
        }
        
        if ($this->box) {
            return $this->box->getPrix();
        }
        
        return 0;
    }
}