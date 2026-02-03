<?php

namespace App\DataFixtures;

use App\Entity\Adresse;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AdresseFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Créer 2-3 adresses pour chaque utilisateur
        for ($i = 0; $i < 15; $i++) {
            /** @var User $user */
            $user = $this->getReference('user_' . $i, User::class); // ✅ DEUX PARAMÈTRES
            
            // Nombre aléatoire d'adresses (1 à 3)
            $nombreAdresses = $faker->numberBetween(1, 3);
            
            for ($j = 0; $j < $nombreAdresses; $j++) {
                $adresse = new Adresse();
                $adresse->setUser($user);
                
                // Types d'adresses
                $types = ['Maison', 'Bureau', 'Chez mes parents', 'Résidence secondaire'];
                $adresse->setNom($types[$j] ?? 'Adresse ' . ($j + 1));
                
                $adresse->setRue($faker->streetAddress);
                $adresse->setCodePostal($faker->postcode);
                $adresse->setVille($faker->city);
                $adresse->setPays('France');
                
                // Complément optionnel
                if ($faker->boolean(30)) {
                    $adresse->setComplement($faker->secondaryAddress);
                }
                
                // La première adresse est par défaut
                $adresse->setParDefaut($j === 0);
                
                $manager->persist($adresse);
                
                // Référence pour les commandes
                if ($j === 0) {
                    $this->addReference('adresse_user_' . $i, $adresse);
                }
            }
        }

        // Adresse pour l'admin
        /** @var User $admin */
        $admin = $this->getReference('user_admin', User::class); // ✅ DEUX PARAMÈTRES
        $adresseAdmin = new Adresse();
        $adresseAdmin->setUser($admin);
        $adresseAdmin->setNom('Siège social');
        $adresseAdmin->setRue('10 rue de la Paix');
        $adresseAdmin->setCodePostal('75001');
        $adresseAdmin->setVille('Paris');
        $adresseAdmin->setPays('France');
        $adresseAdmin->setParDefaut(true);
        
        $manager->persist($adresseAdmin);
        $this->addReference('adresse_admin', $adresseAdmin);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}