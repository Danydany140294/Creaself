<?php

namespace App\Repository;

use App\Entity\Adresse;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Adresse>
 */
class AdresseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Adresse::class);
    }

    /**
     * Récupère toutes les adresses d'un utilisateur
     * 
     * @return Adresse[]
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.user = :user')
            ->setParameter('user', $user)
            ->orderBy('a.parDefaut', 'DESC')
            ->addOrderBy('a.dateCreation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère l'adresse par défaut d'un utilisateur
     */
    public function findAdresseParDefaut(User $user): ?Adresse
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.user = :user')
            ->andWhere('a.parDefaut = :defaut')
            ->setParameter('user', $user)
            ->setParameter('defaut', true)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Définit une adresse comme adresse par défaut
     * (et retire le flag des autres adresses de l'utilisateur)
     */
    public function setAsDefault(Adresse $adresse): void
    {
        $em = $this->getEntityManager();
        
        // Retirer le flag "par défaut" de toutes les adresses de l'utilisateur
        $this->createQueryBuilder('a')
            ->update()
            ->set('a.parDefaut', ':false')
            ->where('a.user = :user')
            ->setParameter('false', false)
            ->setParameter('user', $adresse->getUser())
            ->getQuery()
            ->execute();
        
        // Définir cette adresse comme par défaut
        $adresse->setParDefaut(true);
        $em->persist($adresse);
        $em->flush();
    }

    /**
     * Compte le nombre d'adresses d'un utilisateur
     */
    public function countByUser(User $user): int
    {
        return $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->andWhere('a.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Recherche des adresses par ville
     * 
     * @return Adresse[]
     */
    public function findByVille(string $ville): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.ville LIKE :ville')
            ->setParameter('ville', '%' . $ville . '%')
            ->orderBy('a.ville', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche des adresses par code postal
     * 
     * @return Adresse[]
     */
    public function findByCodePostal(string $codePostal): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.codePostal = :cp')
            ->setParameter('cp', $codePostal)
            ->getQuery()
            ->getResult();
    }

    /**
     * Supprime une adresse et réaffecte une autre comme par défaut si nécessaire
     */
    public function deleteAndReassignDefault(Adresse $adresse): void
    {
        $user = $adresse->getUser();
        $wasDefault = $adresse->isParDefaut();
        
        $em = $this->getEntityManager();
        $em->remove($adresse);
        $em->flush();
        
        // Si c'était l'adresse par défaut, définir une autre adresse comme par défaut
        if ($wasDefault) {
            $autresAdresses = $this->findByUser($user);
            if (count($autresAdresses) > 0) {
                $this->setAsDefault($autresAdresses[0]);
            }
        }
    }

    /**
     * Vérifie si un utilisateur a au moins une adresse
     */
    public function hasAdresse(User $user): bool
    {
        return $this->countByUser($user) > 0;
    }

    //    /**
    //     * @return Adresse[] Returns an array of Adresse objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Adresse
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}