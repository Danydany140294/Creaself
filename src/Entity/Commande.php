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

    #[ORM\Column]
    private ?\DateTime $dateCommande = null;

    #[ORM\Column(length: 50, enumType: CommandeStatut::class)]
    private ?CommandeStatut $statut = null;

    #[ORM\Column]
    private ?float $totalTTC = null;

    #[ORM\ManyToOne]
    private ?User $user = null;

    /**
     * @var Collection<int, LigneCommande>
     */
    #[ORM\OneToMany(targetEntity: LigneCommande::class, mappedBy: 'commande', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $lignesCommande;

    public function __construct()
    {
        $this->lignesCommande = new ArrayCollection();
        $this->dateCommande = new \DateTime();
        $this->statut = CommandeStatut::EN_ATTENTE;
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
     * Alias pour compatibilité avec DashboardController
     */
    public function getMontantTotal(): ?float
    {
        return $this->totalTTC;
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

    public function calculerTotal(): float
    {
        $total = 0;
        foreach ($this->lignesCommande as $ligne) {
            $total += $ligne->getSousTotal();
        }
        return $total;
    }

    /**
     * Pour l'affichage dans EasyAdmin
     */
    public function __toString(): string
    {
        return $this->numeroCommande ?? 'Commande #' . $this->id;
    }
}