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

    #[ORM\Column(nullable: true)]
    private ?int $tailleBox = null;

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

    public function getTailleBox(): ?int
    {
        return $this->tailleBox;
    }

    public function setTailleBox(?int $tailleBox): static
    {
        $this->tailleBox = $tailleBox;

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
    return $this->quantite * $this->getPrixUnitaire();
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
    private const PRIX_BOX_PERSO = [
    6  => 12.00,
    12 => 24.00,
    24 => 30.00,
];

public function getPrixUnitaire(): float
{
    if ($this->produit) {
        return $this->produit->getPrix();
    }

    if ($this->box) {
        // Box personnalisable avec taille connue → prix dynamique
        if ($this->box->getType() === 'personnalisable' && $this->tailleBox !== null) {
            return self::PRIX_BOX_PERSO[$this->tailleBox] ?? $this->box->getPrix();
        }
        return $this->box->getPrix();
    }

    return 0;
}
}
