<?php
// src/Entity/MoyenPaiement.php

namespace App\Entity;

use App\Repository\MoyenPaiementRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MoyenPaiementRepository::class)]
class MoyenPaiement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'moyensPaiement')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    /**
     * Type de paiement : 'stripe_card', 'apple_pay'
     */
    #[ORM\Column(length: 50)]
    private ?string $type = null;

    /**
     * ID du payment method dans Stripe (ex: pm_1234...)
     */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $stripePaymentMethodId = null;

    /**
     * Derniers 4 chiffres de la carte (si carte bancaire)
     */
    #[ORM\Column(length: 4, nullable: true)]
    private ?string $derniers4Chiffres = null;

    /**
     * Type de carte : Visa, Mastercard, Amex (si carte bancaire)
     */
    #[ORM\Column(length: 20, nullable: true)]
    private ?string $marque = null;

    /**
     * Date d'expiration MM/YYYY (si carte bancaire)
     */
    #[ORM\Column(length: 7, nullable: true)]
    private ?string $expiration = null;

    /**
     * Nom affiché (ex: "Ma Visa principale", "Apple Pay")
     */
    #[ORM\Column(length: 100, nullable: true)]
    private ?string $nom = null;

    #[ORM\Column]
    private bool $parDefaut = false;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $dateAjout = null;

    #[ORM\Column]
    private bool $actif = true;

    public function __construct()
    {
        $this->dateAjout = new \DateTime();
        $this->actif = true;
        $this->parDefaut = false;
    }

    // ========================================
    // GETTERS & SETTERS
    // ========================================

    public function getId(): ?int
    {
        return $this->id;
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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getStripePaymentMethodId(): ?string
    {
        return $this->stripePaymentMethodId;
    }

    public function setStripePaymentMethodId(?string $stripePaymentMethodId): static
    {
        $this->stripePaymentMethodId = $stripePaymentMethodId;
        return $this;
    }

    public function getDerniers4Chiffres(): ?string
    {
        return $this->derniers4Chiffres;
    }

    public function setDerniers4Chiffres(?string $derniers4Chiffres): static
    {
        $this->derniers4Chiffres = $derniers4Chiffres;
        return $this;
    }

    public function getMarque(): ?string
    {
        return $this->marque;
    }

    public function setMarque(?string $marque): static
    {
        $this->marque = $marque;
        return $this;
    }

    public function getExpiration(): ?string
    {
        return $this->expiration;
    }

    public function setExpiration(?string $expiration): static
    {
        $this->expiration = $expiration;
        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function isParDefaut(): bool
    {
        return $this->parDefaut;
    }

    public function setParDefaut(bool $parDefaut): static
    {
        $this->parDefaut = $parDefaut;
        return $this;
    }

    public function getDateAjout(): ?\DateTimeInterface
    {
        return $this->dateAjout;
    }

    public function setDateAjout(\DateTimeInterface $dateAjout): static
    {
        $this->dateAjout = $dateAjout;
        return $this;
    }

    public function isActif(): bool
    {
        return $this->actif;
    }

    public function setActif(bool $actif): static
    {
        $this->actif = $actif;
        return $this;
    }

    // ========================================
    // MÉTHODES UTILITAIRES
    // ========================================

    /**
     * Retourne un nom d'affichage convivial
     */
    public function getNomAffichage(): string
    {
        if ($this->nom) {
            return $this->nom;
        }

        return match($this->type) {
            'apple_pay' => 'Apple Pay',
            'stripe_card' => $this->marque ? "{$this->marque} •••• {$this->derniers4Chiffres}" : 'Carte bancaire',
            default => 'Moyen de paiement',
        };
    }

    /**
     * Retourne l'icône Material Symbols à afficher
     */
    public function getIcone(): string
    {
        return match($this->type) {
            'apple_pay' => 'apple',
            'stripe_card' => 'credit_card',
            default => 'payment',
        };
    }

    /**
     * Vérifie si la carte est expirée (uniquement pour les cartes)
     */
    public function isExpire(): bool
    {
        if (!$this->expiration || $this->type !== 'stripe_card') {
            return false;
        }

        try {
            [$mois, $annee] = explode('/', $this->expiration);
            $dateExpiration = new \DateTime("$annee-$mois-01");
            $dateExpiration->modify('last day of this month');
            
            return $dateExpiration < new \DateTime();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Retourne la classe CSS du badge selon le type
     */
    public function getBadgeClass(): string
    {
        if ($this->isExpire()) {
            return 'badge-danger';
        }

        if ($this->parDefaut) {
            return 'badge-success';
        }

        return 'badge-secondary';
    }

    public function __toString(): string
    {
        return $this->getNomAffichage();
    }
}