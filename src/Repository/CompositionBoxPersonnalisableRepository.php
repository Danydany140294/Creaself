<?php

namespace App\Repository;

use App\Entity\CompositionBoxPersonnalisable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CompositionBoxPersonnalisable>
 */
class CompositionBoxPersonnalisableRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CompositionBoxPersonnalisable::class);
    }
}