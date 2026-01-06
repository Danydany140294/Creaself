<?php

namespace App\Repository;

use App\Entity\Panier;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Panier>
 */
class PanierRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Panier::class);
    }

    /**
     * Trouve le panier d'un utilisateur
     */
    public function findByUser(User $user): ?Panier
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Trouve tous les paniers expirés
     */
    public function findExpired(): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.dateExpiration < :now')
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->getResult();
    }

    /**
     * Supprime tous les paniers expirés
     */
    public function deleteExpired(): int
    {
        return $this->createQueryBuilder('p')
            ->delete()
            ->andWhere('p.dateExpiration < :now')
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->execute();
    }
}