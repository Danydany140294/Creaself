<?php
// src/Repository/MoyenPaiementRepository.php

namespace App\Repository;

use App\Entity\MoyenPaiement;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MoyenPaiement>
 */
class MoyenPaiementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MoyenPaiement::class);
    }

    /**
     * Trouve tous les moyens de paiement actifs d'un utilisateur
     *
     * @return MoyenPaiement[]
     */
    public function findActifsByUser(User $user): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.user = :user')
            ->andWhere('m.actif = :actif')
            ->setParameter('user', $user)
            ->setParameter('actif', true)
            ->orderBy('m.parDefaut', 'DESC')
            ->addOrderBy('m.dateAjout', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve le moyen de paiement par défaut d'un utilisateur
     */
    public function findDefaultByUser(User $user): ?MoyenPaiement
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.user = :user')
            ->andWhere('m.parDefaut = :defaut')
            ->andWhere('m.actif = :actif')
            ->setParameter('user', $user)
            ->setParameter('defaut', true)
            ->setParameter('actif', true)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Trouve un moyen de paiement par son Stripe Payment Method ID
     */
    public function findByStripePaymentMethodId(string $paymentMethodId): ?MoyenPaiement
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.stripePaymentMethodId = :paymentMethodId')
            ->setParameter('paymentMethodId', $paymentMethodId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Compte le nombre de moyens de paiement actifs d'un utilisateur
     */
    public function countActifsByUser(User $user): int
    {
        return (int) $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->andWhere('m.user = :user')
            ->andWhere('m.actif = :actif')
            ->setParameter('user', $user)
            ->setParameter('actif', true)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Trouve tous les moyens de paiement expirés
     *
     * @return MoyenPaiement[]
     */
    public function findExpiredCards(): array
    {
        $now = new \DateTime();
        $currentMonth = $now->format('m');
        $currentYear = $now->format('Y');
        
        return $this->createQueryBuilder('m')
            ->andWhere('m.type = :type')
            ->andWhere('m.actif = :actif')
            ->andWhere('m.expiration IS NOT NULL')
            ->andWhere('m.expiration < :expiration')
            ->setParameter('type', 'stripe_card')
            ->setParameter('actif', true)
            ->setParameter('expiration', sprintf('%s/%s', $currentMonth, $currentYear))
            ->getQuery()
            ->getResult();
    }

    /**
     * Désactive tous les moyens de paiement "par défaut" d'un utilisateur
     * (utile avant d'en définir un nouveau comme défaut)
     */
    public function removeDefaultForUser(User $user): int
    {
        return $this->createQueryBuilder('m')
            ->update()
            ->set('m.parDefaut', ':false')
            ->where('m.user = :user')
            ->setParameter('false', false)
            ->setParameter('user', $user)
            ->getQuery()
            ->execute();
    }

    /**
     * Supprime tous les moyens de paiement inactifs d'un utilisateur
     */
    public function deleteInactifsByUser(User $user): int
    {
        return $this->createQueryBuilder('m')
            ->delete()
            ->where('m.user = :user')
            ->andWhere('m.actif = :actif')
            ->setParameter('user', $user)
            ->setParameter('actif', false)
            ->getQuery()
            ->execute();
    }

    //    /**
    //     * @return MoyenPaiement[] Returns an array of MoyenPaiement objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('m.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?MoyenPaiement
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}