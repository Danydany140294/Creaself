<?php

namespace App\Repository;

use App\Entity\Commande;
use App\Entity\User;
use App\Enum\CommandeStatut;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Commande>
 */
class CommandeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commande::class);
    }

    /**
     * Trouve toutes les commandes d'un utilisateur
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.user = :user')
            ->setParameter('user', $user)
            ->orderBy('c.dateCommande', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les commandes par statut
     */
    public function findByStatut(CommandeStatut $statut): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.statut = :statut')
            ->setParameter('statut', $statut)
            ->orderBy('c.dateCommande', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les commandes d'un utilisateur par statut
     */
    public function findByUserAndStatut(User $user, CommandeStatut $statut): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.user = :user')
            ->andWhere('c.statut = :statut')
            ->setParameter('user', $user)
            ->setParameter('statut', $statut)
            ->orderBy('c.dateCommande', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve une commande par son numéro
     */
    public function findByNumeroCommande(string $numeroCommande): ?Commande
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.numeroCommande = :numero')
            ->setParameter('numero', $numeroCommande)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Trouve les commandes récentes (avec lignes et produits)
     */
    public function findRecentWithDetails(int $limit = 10): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.lignesCommande', 'lc')
            ->addSelect('lc')
            ->leftJoin('lc.produit', 'p')
            ->addSelect('p')
            ->leftJoin('lc.box', 'b')
            ->addSelect('b')
            ->orderBy('c.dateCommande', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Calcule le total des commandes par statut
     */
    public function countByStatut(CommandeStatut $statut): int
    {
        return $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->andWhere('c.statut = :statut')
            ->setParameter('statut', $statut)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Calcule le chiffre d'affaires total
     */
    public function calculateTotalRevenue(): float
    {
        $result = $this->createQueryBuilder('c')
            ->select('SUM(c.totalTTC)')
            ->andWhere('c.statut != :annulee')
            ->setParameter('annulee', CommandeStatut::ANNULEE)
            ->getQuery()
            ->getSingleScalarResult();

        return $result ?? 0.0;
    }

    /**
     * Trouve les commandes entre deux dates
     */
    public function findBetweenDates(\DateTime $dateDebut, \DateTime $dateFin): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.dateCommande BETWEEN :debut AND :fin')
            ->setParameter('debut', $dateDebut)
            ->setParameter('fin', $dateFin)
            ->orderBy('c.dateCommande', 'DESC')
            ->getQuery()
            ->getResult();
    }
}