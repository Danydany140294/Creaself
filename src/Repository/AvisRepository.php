<?php

namespace App\Repository;

use App\Entity\Avis;
use App\Entity\Produit;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Avis>
 */
class AvisRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Avis::class);
    }

    /**
     * Récupère tous les avis d'un utilisateur
     * 
     * @return Avis[]
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.user = :user')
            ->setParameter('user', $user)
            ->orderBy('a.dateAvis', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère tous les avis approuvés d'un produit
     * 
     * @return Avis[]
     */
    public function findByProduitApprouves(Produit $produit): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.produit = :produit')
            ->andWhere('a.approuve = :approuve')
            ->andWhere('a.visible = :visible')
            ->setParameter('produit', $produit)
            ->setParameter('approuve', true)
            ->setParameter('visible', true)
            ->orderBy('a.dateAvis', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère tous les avis d'un produit (même non approuvés)
     * 
     * @return Avis[]
     */
    public function findByProduit(Produit $produit): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.produit = :produit')
            ->setParameter('produit', $produit)
            ->orderBy('a.dateAvis', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Calcule la note moyenne d'un produit
     */
    public function getNoteMoyenne(Produit $produit): float
    {
        $result = $this->createQueryBuilder('a')
            ->select('AVG(a.note) as moyenne')
            ->andWhere('a.produit = :produit')
            ->andWhere('a.approuve = :approuve')
            ->andWhere('a.visible = :visible')
            ->setParameter('produit', $produit)
            ->setParameter('approuve', true)
            ->setParameter('visible', true)
            ->getQuery()
            ->getSingleScalarResult();
        
        return round($result ?? 0, 1);
    }

    /**
     * Compte le nombre d'avis approuvés d'un produit
     */
    public function countByProduit(Produit $produit): int
    {
        return $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->andWhere('a.produit = :produit')
            ->andWhere('a.approuve = :approuve')
            ->andWhere('a.visible = :visible')
            ->setParameter('produit', $produit)
            ->setParameter('approuve', true)
            ->setParameter('visible', true)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Récupère les avis récents (approuvés)
     * 
     * @return Avis[]
     */
    public function findRecents(int $limit = 10): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.approuve = :approuve')
            ->andWhere('a.visible = :visible')
            ->setParameter('approuve', true)
            ->setParameter('visible', true)
            ->orderBy('a.dateAvis', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les meilleurs avis (note 5)
     * 
     * @return Avis[]
     */
    public function findMeilleursAvis(int $limit = 5): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.note = :note')
            ->andWhere('a.approuve = :approuve')
            ->andWhere('a.visible = :visible')
            ->andWhere('a.commentaire IS NOT NULL')
            ->setParameter('note', 5)
            ->setParameter('approuve', true)
            ->setParameter('visible', true)
            ->orderBy('a.dateAvis', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les avis en attente d'approbation
     * 
     * @return Avis[]
     */
    public function findEnAttenteApprobation(): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.approuve = :approuve')
            ->setParameter('approuve', false)
            ->orderBy('a.dateAvis', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Vérifie si un utilisateur a déjà laissé un avis sur un produit
     */
    public function hasAvisForProduit(User $user, Produit $produit): bool
    {
        $count = $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->andWhere('a.user = :user')
            ->andWhere('a.produit = :produit')
            ->setParameter('user', $user)
            ->setParameter('produit', $produit)
            ->getQuery()
            ->getSingleScalarResult();
        
        return $count > 0;
    }

    /**
     * Récupère la répartition des notes pour un produit
     * 
     * @return array ['1' => 2, '2' => 5, '3' => 10, '4' => 15, '5' => 20]
     */
    public function getRepartitionNotes(Produit $produit): array
    {
        $results = $this->createQueryBuilder('a')
            ->select('a.note, COUNT(a.id) as nombre')
            ->andWhere('a.produit = :produit')
            ->andWhere('a.approuve = :approuve')
            ->andWhere('a.visible = :visible')
            ->setParameter('produit', $produit)
            ->setParameter('approuve', true)
            ->setParameter('visible', true)
            ->groupBy('a.note')
            ->getQuery()
            ->getResult();
        
        // Initialiser avec 0 pour toutes les notes
        $repartition = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
        
        foreach ($results as $result) {
            $repartition[$result['note']] = (int) $result['nombre'];
        }
        
        return $repartition;
    }

    /**
     * Récupère les statistiques d'avis pour un produit
     * 
     * @return array ['moyenne' => 4.5, 'total' => 42, 'repartition' => [...]]
     */
    public function getStatistiquesProduit(Produit $produit): array
    {
        return [
            'moyenne' => $this->getNoteMoyenne($produit),
            'total' => $this->countByProduit($produit),
            'repartition' => $this->getRepartitionNotes($produit),
        ];
    }

    /**
     * Compte le nombre total d'avis d'un utilisateur
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

    //    /**
    //     * @return Avis[] Returns an array of Avis objects
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

    //    public function findOneBySomeField($value): ?Avis
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}