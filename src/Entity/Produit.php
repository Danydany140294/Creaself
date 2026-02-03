<?php

namespace App\Entity;

use App\Repository\ProduitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProduitRepository::class)]
class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?float $prix = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column]
    private ?int $stock = null;

    #[ORM\Column(length: 255)]
    private ?string $image = null;

    #[ORM\Column(options: ['default' => true])]
    private bool $disponible = true;

    #[ORM\Column(options: ['default' => true])]
    private bool $isActive = true;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $dateModification = null;

    // ========================================
    // RELATIONS
    // ========================================

    /**
     * @var Collection<int, Box>
     * EXTRA_LAZY empêche le chargement automatique des boxes → évite la boucle infinie
     */
    #[ORM\ManyToMany(targetEntity: Box::class, mappedBy: 'produits', fetch: 'EXTRA_LAZY')]
    private Collection $boxes;

    /**
     * @var Collection<int, Avis>
     */
    #[ORM\OneToMany(targetEntity: Avis::class, mappedBy: 'produit', cascade: ['remove'])]
    private Collection $avis;

    /**
     * @var Collection<int, User>
     * Utilisateurs qui ont mis ce produit en favori
     */
    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'favoris')]
    private Collection $utilisateursFavoris;

    public function __construct()
    {
        $this->boxes = new ArrayCollection();
        $this->avis = new ArrayCollection();
        $this->utilisateursFavoris = new ArrayCollection();
        $this->dateCreation = new \DateTime();
    }

    // ========================================
    // GETTERS & SETTERS BASIQUES
    // ========================================

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        $this->dateModification = new \DateTime();
        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): static
    {
        $this->prix = $prix;
        $this->dateModification = new \DateTime();
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        $this->dateModification = new \DateTime();
        return $this;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(int $stock): static
    {
        $this->stock = $stock;
        $this->dateModification = new \DateTime();
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): static
    {
        $this->image = $image;
        $this->dateModification = new \DateTime();
        return $this;
    }

    public function isDisponible(): bool
    {
        return $this->disponible;
    }

    public function setDisponible(bool $disponible): static
    {
        $this->disponible = $disponible;
        $this->dateModification = new \DateTime();
        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;
        $this->dateModification = new \DateTime();
        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): static
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }

    public function getDateModification(): ?\DateTimeInterface
    {
        return $this->dateModification;
    }

    public function setDateModification(?\DateTimeInterface $dateModification): static
    {
        $this->dateModification = $dateModification;
        return $this;
    }

    // ========================================
    // GESTION DES BOXES
    // ========================================

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
            $box->addProduit($this);
        }
        return $this;
    }

    public function removeBox(Box $box): static
    {
        if ($this->boxes->removeElement($box)) {
            $box->removeProduit($this);
        }
        return $this;
    }

    // ========================================
    // GESTION DES AVIS
    // ========================================

    /**
     * @return Collection<int, Avis>
     */
    public function getAvis(): Collection
    {
        return $this->avis;
    }

    public function addAvi(Avis $avi): static
    {
        if (!$this->avis->contains($avi)) {
            $this->avis->add($avi);
            $avi->setProduit($this);
        }
        return $this;
    }

    public function removeAvi(Avis $avi): static
    {
        if ($this->avis->removeElement($avi)) {
            if ($avi->getProduit() === $this) {
                $avi->setProduit(null);
            }
        }
        return $this;
    }

    /**
     * Compte le nombre d'avis approuvés
     */
    public function getNombreAvis(): int
    {
        return $this->avis->filter(function($avis) {
            return $avis->isApprouve() && $avis->isVisible();
        })->count();
    }

    /**
     * Retourne les avis approuvés uniquement
     * 
     * @return Collection<int, Avis>
     */
    public function getAvisApprouves(): Collection
    {
        return $this->avis->filter(function($avis) {
            return $avis->isApprouve() && $avis->isVisible();
        });
    }

    /**
     * Calcule la note moyenne du produit
     */
    public function getNoteMoyenne(): float
    {
        $avisApprouves = $this->getAvisApprouves();
        
        if ($avisApprouves->isEmpty()) {
            return 0;
        }
        
        $total = 0;
        foreach ($avisApprouves as $avis) {
            $total += $avis->getNote();
        }
        
        return round($total / $avisApprouves->count(), 1);
    }

    // ========================================
    // GESTION DES FAVORIS
    // ========================================

    /**
     * @return Collection<int, User>
     */
    public function getUtilisateursFavoris(): Collection
    {
        return $this->utilisateursFavoris;
    }

    public function addUtilisateurFavori(User $utilisateur): static
    {
        if (!$this->utilisateursFavoris->contains($utilisateur)) {
            $this->utilisateursFavoris->add($utilisateur);
            $utilisateur->addFavori($this);
        }
        return $this;
    }

    public function removeUtilisateurFavori(User $utilisateur): static
    {
        if ($this->utilisateursFavoris->removeElement($utilisateur)) {
            $utilisateur->removeFavori($this);
        }
        return $this;
    }

    /**
     * Compte le nombre d'utilisateurs ayant mis ce produit en favori
     */
    public function getNombreFavoris(): int
    {
        return $this->utilisateursFavoris->count();
    }

    /**
     * Vérifie si un utilisateur a mis ce produit en favori
     */
    public function isFavoriPar(User $utilisateur): bool
    {
        return $this->utilisateursFavoris->contains($utilisateur);
    }

    // ========================================
    // MÉTHODES MÉTIER
    // ========================================

    /**
     * Retourne le prix formaté avec devise
     */
    public function getPrixFormatte(): string
    {
        return number_format($this->prix, 2, ',', ' ') . ' €';
    }

    /**
     * Vérifie si le produit est en stock
     */
    public function isEnStock(): bool
    {
        return $this->stock > 0;
    }

    /**
     * Vérifie si le produit est en rupture de stock
     */
    public function isRuptureStock(): bool
    {
        return $this->stock === 0;
    }

    /**
     * Vérifie si le stock est faible (moins de 10)
     */
    public function isStockFaible(): bool
    {
        return $this->stock > 0 && $this->stock < 10;
    }

    /**
     * Vérifie si le produit peut être acheté
     */
    public function isAchetable(): bool
    {
        return $this->isActive && $this->disponible && $this->isEnStock();
    }

    /**
     * Retourne le message de disponibilité
     */
    public function getMessageDisponibilite(): string
    {
        if (!$this->isActive) {
            return 'Produit indisponible';
        }
        
        if (!$this->disponible) {
            return 'Temporairement indisponible';
        }
        
        if ($this->isRuptureStock()) {
            return 'Rupture de stock';
        }
        
        if ($this->isStockFaible()) {
            return sprintf('Plus que %d en stock !', $this->stock);
        }
        
        return 'En stock';
    }

    /**
     * Retourne la classe CSS du badge de disponibilité
     */
    public function getBadgeDisponibiliteClass(): string
    {
        if (!$this->isActive || !$this->disponible) {
            return 'badge-danger';
        }
        
        if ($this->isRuptureStock()) {
            return 'badge-danger';
        }
        
        if ($this->isStockFaible()) {
            return 'badge-warning';
        }
        
        return 'badge-success';
    }

    /**
     * Retourne une description courte (100 caractères max)
     */
    public function getDescriptionCourte(int $longueur = 100): string
    {
        if (!$this->description) {
            return '';
        }
        
        if (mb_strlen($this->description) <= $longueur) {
            return $this->description;
        }
        
        return mb_substr($this->description, 0, $longueur) . '...';
    }

    /**
     * Décrémente le stock (lors d'un achat)
     */
    public function decrémenterStock(int $quantite = 1): static
    {
        $this->stock = max(0, $this->stock - $quantite);
        $this->dateModification = new \DateTime();
        
        if ($this->stock === 0) {
            $this->disponible = false;
        }
        
        return $this;
    }

    /**
     * Incrémente le stock (lors d'un réapprovisionnement)
     */
    public function incrementerStock(int $quantite = 1): static
    {
        $this->stock += $quantite;
        $this->dateModification = new \DateTime();
        
        if ($this->stock > 0 && !$this->disponible) {
            $this->disponible = true;
        }
        
        return $this;
    }

    /**
     * Vérifie si le produit est nouveau (moins de 30 jours)
     */
    public function isNouveau(): bool
    {
        if (!$this->dateCreation) {
            return false;
        }
        
        $maintenant = new \DateTime();
        $diff = $maintenant->diff($this->dateCreation);
        
        return $diff->days < 30;
    }

    /**
     * Vérifie si le produit est populaire (plus de 10 favoris)
     */
    public function isPopulaire(): bool
    {
        return $this->getNombreFavoris() >= 10;
    }

    /**
     * Retourne les badges du produit (nouveau, populaire, etc.)
     * 
     * @return array<string>
     */
    public function getBadges(): array
    {
        $badges = [];
        
        if ($this->isNouveau()) {
            $badges[] = 'Nouveau';
        }
        
        if ($this->isPopulaire()) {
            $badges[] = 'Populaire';
        }
        
        if ($this->getNoteMoyenne() >= 4.5) {
            $badges[] = 'Excellent';
        }
        
        if ($this->isStockFaible()) {
            $badges[] = 'Stock limité';
        }
        
        return $badges;
    }

    /**
     * Pour l'affichage dans EasyAdmin et les formulaires
     */
    public function __toString(): string
    {
        return $this->name ?? 'Produit #' . $this->id;
    }
}