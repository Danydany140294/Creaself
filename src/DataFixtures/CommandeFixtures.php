<?php

namespace App\DataFixtures;

use App\Entity\Commande;
use App\Entity\User;
use App\Entity\Produit;
use App\Entity\Box;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class CommandeFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        
        // Récupérer les utilisateurs, produits et box
        $userRepository = $manager->getRepository(User::class);
        $produitRepository = $manager->getRepository(Produit::class);
        $boxRepository = $manager->getRepository(Box::class);
        
        $users = $userRepository->findAll();
        $produits = $produitRepository->findAll();
        $boxes = $boxRepository->findAll();
        
        $statuts = ['en_attente', 'validee', 'expediee', 'livree'];
        
        // Créer 15 commandes
        for ($i = 1; $i <= 15; $i++) {
            $commande = new Commande();
            
            // Numéro de commande unique
            $commande->setNumeroCommande('CMD-' . date('Y') . '-' . str_pad($i, 5, '0', STR_PAD_LEFT));
            
            // Date aléatoire dans les 3 derniers mois
            $commande->setDateCommande($faker->dateTimeBetween('-3 months', 'now'));
            
            // Statut aléatoire
            $commande->setStatut($faker->randomElement($statuts));
            
            // Associer à un utilisateur aléatoire
            if (!empty($users)) {
                $commande->setUser($faker->randomElement($users));
            }
            
            $total = 0;
            
            // Ajouter soit des produits, soit des box, ou les deux
            $type = $faker->numberBetween(1, 3);
            
            if ($type == 1 || $type == 3) {
                // Ajouter 1 à 4 produits aléatoires
                $nbProduits = $faker->numberBetween(1, 4);
                $produitsAleatoires = $faker->randomElements($produits, min($nbProduits, count($produits)));
                
                foreach ($produitsAleatoires as $produit) {
                    $commande->addProduit($produit);
                    $total += $produit->getPrix();
                }
            }
            
            if ($type == 2 || $type == 3) {
                // Ajouter 1 à 2 box aléatoires
                $nbBoxes = $faker->numberBetween(1, 2);
                $boxesAleatoires = $faker->randomElements($boxes, min($nbBoxes, count($boxes)));
                
                foreach ($boxesAleatoires as $box) {
                    $commande->addBox($box);
                    $total += $box->getPrix();
                }
            }
            
            // Définir le total
            $commande->setTotalTTC(round($total, 2));
            
            $manager->persist($commande);
        }
        
        $manager->flush();
    }
    
    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            ProduitFixtures::class,
            BoxFixtures::class,
        ];
    }
}