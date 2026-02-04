<?php

namespace App\Entity;

use App\Repository\AdresseRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AdresseRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Adresse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Le nom de l\'adresse est requis')]
    #[Assert\Length(
        min: 2,
        max: 100,
        minMessage: 'Le nom doit contenir au moins {{ limit }} caractères',
        maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères'
    )]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'L\'adresse est requise')]
    private ?string $rue = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $complement = null;

    #[ORM\Column(length: 10)]
    #[Assert\NotBlank(message: 'Le code postal est requis')]
    #[Assert\Regex(
        pattern: '/^[0-9]{5}$/',
        message: 'Le code postal doit contenir 5 chiffres'
    )]
    private ?string $codePostal = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'La ville est requise')]
    private ?string $ville = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Le pays est requis')]
    private ?string $pays = 'France';

    #[ORM\Column]
    private ?bool $parDefaut = false;

    #[ORM\ManyToOne(inversedBy: 'adresses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->parDefaut = false;
        $this->pays = 'France';
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    // ========================================
    // GETTERS & SETTERS
    // ========================================

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function getRue(): ?string
    {
        return $this->rue;
    }

    public function setRue(string $rue): static
    {
        $this->rue = $rue;
        return $this;
    }

    public function getComplement(): ?string
    {
        return $this->complement;
    }

    public function setComplement(?string $complement): static
    {
        $this->complement = $complement;
        return $this;
    }

    public function getCodePostal(): ?string
    {
        return $this->codePostal;
    }

    public function setCodePostal(string $codePostal): static
    {
        $this->codePostal = $codePostal;
        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(string $ville): static
    {
        $this->ville = $ville;
        return $this;
    }

    public function getPays(): ?string
    {
        return $this->pays;
    }

    public function setPays(string $pays): static
    {
        $this->pays = $pays;
        return $this;
    }

    public function isParDefaut(): ?bool
    {
        return $this->parDefaut;
    }

    public function setParDefaut(bool $parDefaut): static
    {
        $this->parDefaut = $parDefaut;
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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    // ========================================
    // MÉTHODES UTILITAIRES
    // ========================================

    /**
     * Retourne l'adresse complète formatée sur une ligne
     */
    public function getAdresseComplete(): string
    {
        $adresse = $this->rue;
        
        if ($this->complement) {
            $adresse .= ', ' . $this->complement;
        }
        
        $adresse .= ', ' . $this->codePostal . ' ' . $this->ville;
        $adresse .= ', ' . $this->pays;
        
        return $adresse;
    }

    /**
     * Retourne l'adresse formatée en plusieurs lignes (pour affichage)
     */
    public function getAdresseFormatee(): array
    {
        $lignes = [$this->rue];
        
        if ($this->complement) {
            $lignes[] = $this->complement;
        }
        
        $lignes[] = $this->codePostal . ' ' . $this->ville;
        $lignes[] = $this->pays;
        
        return $lignes;
    }

    /**
     * Retourne une version courte de l'adresse
     */
    public function getAdresseCourte(): string
    {
        return $this->ville . ' (' . $this->codePostal . ')';
    }

    /**
     * Représentation string de l'adresse
     */
    public function __toString(): string
    {
        return $this->nom . ' - ' . $this->getAdresseCourte();
    }
}