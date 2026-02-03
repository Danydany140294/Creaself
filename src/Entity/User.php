<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    private ?string $Nom = null;

    #[ORM\Column(length: 255)]
    private ?string $Prenom = null;

    #[ORM\Column(length: 20)]
    private ?string $Telephone = null;

    // ⭐ NOUVEAUX CHAMPS POUR LE DASHBOARD
    
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $avatar = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $dateNaissance = null;

    #[ORM\Column(options: ['default' => 0])]
    private int $pointsFidelite = 0;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $dateInscription = null;

    // ⭐ RELATIONS POUR LE DASHBOARD

    /**
     * @var Collection<int, Commande>
     */
    #[ORM\OneToMany(targetEntity: Commande::class, mappedBy: 'user', cascade: ['persist'])]
    #[ORM\OrderBy(['dateCommande' => 'DESC'])]
    private Collection $commandes;

    /**
     * @var Collection<int, Adresse>
     */
    #[ORM\OneToMany(targetEntity: Adresse::class, mappedBy: 'user', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $adresses;

    /**
     * @var Collection<int, Avis>
     */
    #[ORM\OneToMany(targetEntity: Avis::class, mappedBy: 'user', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $avis;

    /**
     * @var Collection<int, Produit>
     */
    #[ORM\ManyToMany(targetEntity: Produit::class)]
    #[ORM\JoinTable(name: 'user_favoris')]
    private Collection $favoris;

    public function __construct()
    {
        $this->commandes = new ArrayCollection();
        $this->adresses = new ArrayCollection();
        $this->avis = new ArrayCollection();
        $this->favoris = new ArrayCollection();
        $this->dateInscription = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function eraseCredentials(): void
    {
        // clear temporary sensitive data if any
    }

    public function getNom(): ?string
    {
        return $this->Nom;
    }

    public function setNom(string $Nom): static
    {
        $this->Nom = $Nom;
        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->Prenom;
    }

    public function setPrenom(string $Prenom): static
    {
        $this->Prenom = $Prenom;
        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->Telephone;
    }

    public function setTelephone(string $Telephone): static
    {
        $this->Telephone = $Telephone;
        return $this;
    }

    // ⭐ NOUVEAUX GETTERS/SETTERS

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): static
    {
        $this->avatar = $avatar;
        return $this;
    }

    public function getDateNaissance(): ?\DateTimeInterface
    {
        return $this->dateNaissance;
    }

    public function setDateNaissance(?\DateTimeInterface $dateNaissance): static
    {
        $this->dateNaissance = $dateNaissance;
        return $this;
    }

    public function getPointsFidelite(): int
    {
        return $this->pointsFidelite;
    }

    public function setPointsFidelite(int $pointsFidelite): static
    {
        $this->pointsFidelite = $pointsFidelite;
        return $this;
    }

    public function ajouterPoints(int $points): static
    {
        $this->pointsFidelite += $points;
        return $this;
    }

    public function retirerPoints(int $points): static
    {
        $this->pointsFidelite = max(0, $this->pointsFidelite - $points);
        return $this;
    }

    public function getDateInscription(): ?\DateTimeInterface
    {
        return $this->dateInscription;
    }

    public function setDateInscription(\DateTimeInterface $dateInscription): static
    {
        $this->dateInscription = $dateInscription;
        return $this;
    }

    // ⭐ MÉTHODES POUR LES RELATIONS

    /**
     * @return Collection<int, Commande>
     */
    public function getCommandes(): Collection
    {
        return $this->commandes;
    }

    public function addCommande(Commande $commande): static
    {
        if (!$this->commandes->contains($commande)) {
            $this->commandes->add($commande);
            $commande->setUser($this);
        }
        return $this;
    }

    public function removeCommande(Commande $commande): static
    {
        if ($this->commandes->removeElement($commande)) {
            if ($commande->getUser() === $this) {
                $commande->setUser(null);
            }
        }
        return $this;
    }

    /**
     * Récupère la dernière commande de l'utilisateur
     */
    public function getDerniereCommande(): ?Commande
    {
        if ($this->commandes->isEmpty()) {
            return null;
        }
        return $this->commandes->first() ?: null;
    }

    /**
     * @return Collection<int, Adresse>
     */
    public function getAdresses(): Collection
    {
        return $this->adresses;
    }

    public function addAdresse(Adresse $adresse): static
    {
        if (!$this->adresses->contains($adresse)) {
            $this->adresses->add($adresse);
            $adresse->setUser($this);
        }
        return $this;
    }

    public function removeAdresse(Adresse $adresse): static
    {
        if ($this->adresses->removeElement($adresse)) {
            if ($adresse->getUser() === $this) {
                $adresse->setUser(null);
            }
        }
        return $this;
    }

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
            $avi->setUser($this);
        }
        return $this;
    }

    public function removeAvi(Avis $avi): static
    {
        if ($this->avis->removeElement($avi)) {
            if ($avi->getUser() === $this) {
                $avi->setUser(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Produit>
     */
    public function getFavoris(): Collection
    {
        return $this->favoris;
    }

    public function addFavori(Produit $favori): static
    {
        if (!$this->favoris->contains($favori)) {
            $this->favoris->add($favori);
        }
        return $this;
    }

    public function removeFavori(Produit $favori): static
    {
        $this->favoris->removeElement($favori);
        return $this;
    }

    public function isFavori(Produit $produit): bool
    {
        return $this->favoris->contains($produit);
    }
}