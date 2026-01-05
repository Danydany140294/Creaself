<?php

namespace App\Entity;

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

    #[ORM\Column]
    private ?\DateTime $dateCommande = null;

    #[ORM\Column(length: 50)]
    private ?string $statut = null;

    #[ORM\Column]
    private ?float $totalTTC = null;

    #[ORM\ManyToOne]
    private ?User $user = null;

    /**
     * @var Collection<int, Produit>
     */
    #[ORM\ManyToMany(targetEntity: Produit::class, inversedBy: 'commandes')]
    private Collection $produits;

    /**
     * @var Collection<int, Box>
     */
    #[ORM\ManyToMany(targetEntity: Box::class, inversedBy: 'commandes')]
    private Collection $boxes;

    public function __construct()
    {
        $this->produits = new ArrayCollection();
        $this->boxes = new ArrayCollection();
    }

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

    public function getDateCommande(): ?\DateTime
    {
        return $this->dateCommande;
    }

    public function setDateCommande(\DateTime $dateCommande): static
    {
        $this->dateCommande = $dateCommande;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
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
     * @return Collection<int, Produit>
     */
    public function getProduits(): Collection
    {
        return $this->produits;
    }

    public function addProduit(Produit $produit): static
    {
        if (!$this->produits->contains($produit)) {
            $this->produits->add($produit);
        }

        return $this;
    }

    public function removeProduit(Produit $produit): static
    {
        $this->produits->removeElement($produit);

        return $this;
    }

    /**
     * @return Collection<int, Box>
     */
    public function getBoxes(): Collection
    {
        return $this->boxes;
    }

    public function addBox(Box $box): static
    {
        if (!$this->boxes->contains($box)) {
            $this->boxes->add($box);
        }

        return $this;
    }

    public function removeBox(Box $box): static
    {
        $this->boxes->removeElement($box);

        return $this;
    }
}
