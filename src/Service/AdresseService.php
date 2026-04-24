<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class AdresseService
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    /**
     * Retire le statut "par défaut" de toutes les adresses de l'utilisateur
     */
    public function removeDefaultFromOtherAddresses(User $user): void
    {
        foreach ($user->getAdresses() as $addr) {
            if ($addr->isParDefaut()) {
                $addr->setParDefaut(false);
            }
        }
        $this->em->flush();
    }
}