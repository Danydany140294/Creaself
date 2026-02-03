<?php

namespace App\Entity;

use App\Repository\AvisRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AvisRepository::class)]
class Avis
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $note = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $commentaire = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $dateAvis = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $approuve = false;

    #[ORM\Column(options: ['default' => true])]
    private bool $visible = true;

    #[ORM\ManyToOne(inversedBy: 'avis')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Produit $produit = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?Commande $commande = null;

    public function __construct()
    {
        $this->dateAvis = new \DateTime();
    }

    // ========================================
    // GETTERS & SETTERS BASIQUES
    // ========================================

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNote(): ?int
    {
        return $this->note;
    }

    public function setNote(int $note): static
    {
        // Validation : note entre 1 et 5
        if ($note < 1 || $note > 5) {
            throw new \InvalidArgumentException('La note doit être entre 1 et 5');
        }
        
        $this->note = $note;
        return $this;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): static
    {
        $this->commentaire = $commentaire;
        return $this;
    }

    public function getDateAvis(): ?\DateTimeInterface
    {
        return $this->dateAvis;
    }

    public function setDateAvis(\DateTimeInterface $dateAvis): static
    {
        $this->dateAvis = $dateAvis;
        return $this;
    }

    public function isApprouve(): bool
    {
        return $this->approuve;
    }

    public function setApprouve(bool $approuve): static
    {
        $this->approuve = $approuve;
        return $this;
    }

    public function isVisible(): bool
    {
        return $this->visible;
    }

    public function setVisible(bool $visible): static
    {
        $this->visible = $visible;
        return $this;
    }

    // ========================================
    // RELATIONS
    // ========================================

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
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

    public function getCommande(): ?Commande
    {
        return $this->commande;
    }

    public function setCommande(?Commande $commande): static
    {
        $this->commande = $commande;
        return $this;
    }

    // ========================================
    // MÉTHODES MÉTIER
    // ========================================

    /**
     * Retourne le nom complet de l'utilisateur qui a laissé l'avis
     */
    public function getNomUtilisateur(): string
    {
        if (!$this->user) {
            return 'Utilisateur inconnu';
        }
        
        return $this->user->getPrenom() . ' ' . substr($this->user->getNom(), 0, 1) . '.';
    }

    /**
     * Retourne les initiales de l'utilisateur
     */
    public function getInitialesUtilisateur(): string
    {
        if (!$this->user) {
            return 'XX';
        }
        
        $prenom = $this->user->getPrenom();
        $nom = $this->user->getNom();
        
        return strtoupper(substr($prenom, 0, 1) . substr($nom, 0, 1));
    }

    /**
     * Retourne un tableau d'étoiles pleines/vides pour l'affichage
     * 
     * @return array ['full' => 4, 'empty' => 1]
     */
    public function getEtoiles(): array
    {
        return [
            'full' => $this->note ?? 0,
            'empty' => 5 - ($this->note ?? 0),
        ];
    }

    /**
     * Retourne le commentaire tronqué
     */
    public function getCommentaireCourt(int $longueur = 100): string
    {
        if (!$this->commentaire) {
            return '';
        }
        
        if (mb_strlen($this->commentaire) <= $longueur) {
            return $this->commentaire;
        }
        
        return mb_substr($this->commentaire, 0, $longueur) . '...';
    }

    /**
     * Vérifie si l'avis a un commentaire
     */
    public function hasCommentaire(): bool
    {
        return !empty($this->commentaire);
    }

    /**
     * Retourne une date relative (il y a X jours)
     */
    public function getDateRelative(): string
    {
        if (!$this->dateAvis) {
            return '';
        }
        
        $maintenant = new \DateTime();
        $diff = $maintenant->diff($this->dateAvis);
        
        if ($diff->y > 0) {
            return sprintf('Il y a %d an%s', $diff->y, $diff->y > 1 ? 's' : '');
        }
        
        if ($diff->m > 0) {
            return sprintf('Il y a %d mois', $diff->m);
        }
        
        if ($diff->d > 0) {
            return sprintf('Il y a %d jour%s', $diff->d, $diff->d > 1 ? 's' : '');
        }
        
        if ($diff->h > 0) {
            return sprintf('Il y a %d heure%s', $diff->h, $diff->h > 1 ? 's' : '');
        }
        
        return "Aujourd'hui";
    }

    /**
     * Vérifie si l'avis est récent (moins de 7 jours)
     */
    public function isRecent(): bool
    {
        if (!$this->dateAvis) {
            return false;
        }
        
        $maintenant = new \DateTime();
        $diff = $maintenant->diff($this->dateAvis);
        
        return $diff->days < 7;
    }

    /**
     * Retourne la classe CSS de la note pour l'affichage
     */
    public function getNoteClass(): string
    {
        return match($this->note) {
            5 => 'note-excellent',
            4 => 'note-bien',
            3 => 'note-moyen',
            2 => 'note-passable',
            1 => 'note-mauvais',
            default => 'note-none',
        };
    }

    /**
     * Retourne le libellé de la note
     */
    public function getNoteLabel(): string
    {
        return match($this->note) {
            5 => 'Excellent',
            4 => 'Très bien',
            3 => 'Bien',
            2 => 'Moyen',
            1 => 'Décevant',
            default => 'Non noté',
        };
    }

    /**
     * Vérifie si l'avis peut être modifié (moins de 48h)
     */
    public function isModifiable(): bool
    {
        if (!$this->dateAvis) {
            return false;
        }
        
        $maintenant = new \DateTime();
        $diff = $maintenant->diff($this->dateAvis);
        
        return $diff->days < 2 && !$diff->invert;
    }

    /**
     * Vérifie si l'avis peut être affiché publiquement
     */
    public function isAffichable(): bool
    {
        return $this->visible && $this->approuve;
    }

    /**
     * Pour l'affichage dans EasyAdmin
     */
    public function __toString(): string
    {
        $produitNom = $this->produit ? $this->produit->getName() : 'Produit inconnu';
        $userName = $this->user ? $this->user->getPrenom() : 'Utilisateur inconnu';
        
        return sprintf('Avis de %s sur %s (%d/5)', $userName, $produitNom, $this->note ?? 0);
    }
}