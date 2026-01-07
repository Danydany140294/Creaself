<?php

namespace App\Entity;

use App\Repository\PanierRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PanierRepository::class)]
class Panier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $dateCreation = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $dateExpiration = null;

    /**
     * @var Collection<int, LignePanier>
     */
    #[ORM\OneToMany(targetEntity: LignePanier::class, mappedBy: 'panier', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $lignesPanier;

    public function __construct()
    {
        $this->lignesPanier = new ArrayCollection();
        $this->dateCreation = new \DateTimeImmutable();
        $this->rafraichirExpiration();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeImmutable
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeImmutable $dateCreation): static
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function getDateExpiration(): ?\DateTimeImmutable
    {
        return $this->dateExpiration;
    }

    public function setDateExpiration(\DateTimeImmutable $dateExpiration): static
    {
        $this->dateExpiration = $dateExpiration;

        return $this;
    }

    /**
     * Rafraîchit la date d'expiration à +10 minutes (durée courte pour tests/démos)
     */
    public function rafraichirExpiration(): void
    {
        $this->dateExpiration = new \DateTimeImmutable('+10 minutes');
    }

    /**
     * Vérifie si le panier est expiré
     */
    public function isExpire(): bool
    {
        return new \DateTimeImmutable() > $this->dateExpiration;
    }

    /**
     * @return Collection<int, LignePanier>
     */
    public function getLignesPanier(): Collection
    {
        return $this->lignesPanier;
    }

    public function addLignePanier(LignePanier $lignePanier): static
    {
        if (!$this->lignesPanier->contains($lignePanier)) {
            $this->lignesPanier->add($lignePanier);
            $lignePanier->setPanier($this);
        }

        return $this;
    }

    public function removeLignePanier(LignePanier $lignePanier): static
    {
        if ($this->lignesPanier->removeElement($lignePanier)) {
            if ($lignePanier->getPanier() === $this) {
                $lignePanier->setPanier(null);
            }
        }

        return $this;
    }

    /**
     * Calcule le total du panier
     */
    public function getTotal(): float
    {
        $total = 0;
        foreach ($this->lignesPanier as $ligne) {
            $total += $ligne->getSousTotal();
        }
        return $total;
    }

    /**
     * Compte le nombre total d'articles dans le panier
     */
    public function getNombreArticles(): int
    {
        $total = 0;
        foreach ($this->lignesPanier as $ligne) {
            $total += $ligne->getQuantite();
        }
        return $total;
    }

    /**
     * Vérifie si le panier est vide
     */
    public function isEmpty(): bool
    {
        return $this->lignesPanier->isEmpty();
    }
}