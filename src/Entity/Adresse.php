<?php

namespace App\Entity;

use App\Repository\AdresseRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AdresseRepository::class)]
class Adresse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $rue = null;

    #[ORM\Column(length: 10)]
    private ?string $codePostal = null;

    #[ORM\Column(length: 100)]
    private ?string $ville = null;

    #[ORM\Column(length: 100)]
    private ?string $pays = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $complement = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $parDefaut = false;

    #[ORM\ManyToOne(inversedBy: 'adresses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $dateCreation = null;

    public function __construct()
    {
        $this->dateCreation = new \DateTime();
    }

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

    public function getComplement(): ?string
    {
        return $this->complement;
    }

    public function setComplement(?string $complement): static
    {
        $this->complement = $complement;
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
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

    /**
     * Retourne l'adresse complète formatée
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
     * Retourne l'adresse sur une ligne (pour affichage compact)
     */
    public function getAdresseOneLine(): string
    {
        return $this->rue . ', ' . $this->codePostal . ' ' . $this->ville;
    }

    /**
     * Pour l'affichage dans les formulaires et EasyAdmin
     */
    public function __toString(): string
    {
        return $this->nom . ' - ' . $this->getAdresseOneLine();
    }
}