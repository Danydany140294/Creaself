<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Produit;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class FavorisFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Chaque utilisateur a entre 0 et 5 favoris
        for ($i = 0; $i < 15; $i++) {
            /** @var User $user */
            $user = $this->getReference('user_' . $i, User::class); // ✅ CORRIGÉ
            $nombreFavoris = $faker->numberBetween(0, 5);
            
            $produitsAjoutes = []; // Pour éviter les doublons
            
            for ($j = 0; $j < $nombreFavoris; $j++) {
                // Produit aléatoire parmi les 5 disponibles (0 à 4)
                $produitIndex = $faker->numberBetween(0, 4);
                
                // Éviter d'ajouter le même produit deux fois
                if (in_array($produitIndex, $produitsAjoutes)) {
                    continue;
                }
                
                /** @var Produit $produit */
                $produit = $this->getReference('produit_' . $produitIndex, Produit::class);
                $user->addFavori($produit);
                $produitsAjoutes[] = $produitIndex;
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            ProduitFixtures::class, // ✅ DÉCOMMENTÉ
        ];
    }
}