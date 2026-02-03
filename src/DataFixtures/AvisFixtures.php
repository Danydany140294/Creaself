<?php

namespace App\DataFixtures;

use App\Entity\Avis;
use App\Entity\User;
use App\Entity\Produit;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AvisFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Créer des avis pour les produits
        
        for ($i = 0; $i < 50; $i++) {
            $avis = new Avis();
            
            // Utilisateur aléatoire
            $userIndex = $faker->numberBetween(0, 14);
            $user = $this->getReference('user_' . $userIndex, User::class);
            $avis->setUser($user);
            
            // Produit aléatoire (0 à 4 = 5 produits)
            $produitIndex = $faker->numberBetween(0, 4); // ✅ CORRIGÉ : 5 produits
            $produit = $this->getReference('produit_' . $produitIndex, Produit::class);
            $avis->setProduit($produit);
            
            // Note entre 1 et 5
            $avis->setNote($faker->numberBetween(1, 5));
            
            // Commentaire (80% de chance d'avoir un commentaire)
            if ($faker->boolean(80)) {
                $commentaires = [
                    'Délicieux ! Je recommande vivement.',
                    'Très bon produit, conforme à mes attentes.',
                    'Excellent rapport qualité-prix.',
                    'Un peu déçu, je m\'attendais à mieux.',
                    'Parfait pour un cadeau !',
                    'Mes enfants adorent, je vais en recommander.',
                    'Texture parfaite et goût exquis.',
                    'Livraison rapide et produit bien emballé.',
                    'Je suis fan, c\'est ma deuxième commande !',
                    'Correct sans plus.',
                ];
                $avis->setCommentaire($faker->randomElement($commentaires));
            }
            
            // Date aléatoire dans les 6 derniers mois
            $avis->setDateAvis($faker->dateTimeBetween('-6 months', 'now'));
            
            // 90% des avis sont approuvés
            $avis->setApprouve($faker->boolean(90));
            $avis->setVisible(true);
            
            $manager->persist($avis);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            ProduitFixtures::class,
        ];
    }
}