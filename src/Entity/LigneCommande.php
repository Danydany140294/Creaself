<?php

namespace App\Entity;

use App\Repository\LigneCommandeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LigneCommandeRepository::class)]
class LigneCommande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'lignesCommande')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Commande $commande = null;

    #[ORM\ManyToOne]
    private ?Produit $produit = null;

    #[ORM\ManyToOne]
    private ?Box $box = null;

    #[ORM\Column]
    private ?int $quantite = null;

    #[ORM\Column]
    private ?float $prixUnitaire = null;

    /**
     * @var Collection<int, CompositionBoxPersonnalisable>
     */
    #[ORM\OneToMany(targetEntity: CompositionBoxPersonnalisable::class, mappedBy: 'ligneCommande', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $compositionsBox;

    public function __construct()
    {
        $this->compositionsBox = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommande(): ?Commande
    {
        return $this->commande;
    }

    public function setCommande(?Commande $commande): static
    {
        $this->commande = $commande;

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

    public function getPrixUnitaire(): ?float
    {
        return $this->prixUnitaire;
    }

    public function setPrixUnitaire(float $prixUnitaire): static
    {
        $this->prixUnitaire = $prixUnitaire;

        return $this;
    }

    public function getSousTotal(): float
    {
        return $this->quantite * $this->prixUnitaire;
    }

    /**
     * @return Collection<int, CompositionBoxPersonnalisable>
     */
    public function getCompositionsBox(): Collection
    {
        return $this->compositionsBox;
    }

    public function addCompositionBox(CompositionBoxPersonnalisable $compositionBox): static
    {
        if (!$this->compositionsBox->contains($compositionBox)) {
            $this->compositionsBox->add($compositionBox);
            $compositionBox->setLigneCommande($this);
        }

        return $this;
    }

    public function removeCompositionBox(CompositionBoxPersonnalisable $compositionBox): static
    {
        if ($this->compositionsBox->removeElement($compositionBox)) {
            if ($compositionBox->getLigneCommande() === $this) {
                $compositionBox->setLigneCommande(null);
            }
        }

        return $this;
    }

    /**
     * Vérifie si cette ligne est une box personnalisable
     */
    public function isBoxPersonnalisable(): bool
    {
        return $this->box !== null && 
               $this->box->getType() === 'personnalisable' && 
               !$this->compositionsBox->isEmpty();
    }
}