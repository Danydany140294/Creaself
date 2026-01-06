<?php

namespace App\Repository;

use App\Entity\CompositionPanierPersonnalisable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CompositionPanierPersonnalisable>
 */
class CompositionPanierPersonnalisableRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CompositionPanierPersonnalisable::class);
    }
}