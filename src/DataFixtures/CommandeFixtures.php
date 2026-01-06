<?php

namespace App\DataFixtures;

use App\Entity\Commande;
use App\Entity\CompositionBoxPersonnalisable;
use App\Entity\LigneCommande;
use App\Entity\User;
use App\Entity\Produit;
use App\Entity\Box;
use App\Enum\CommandeStatut;
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
        $boxPersonnalisable = $boxRepository->findOneBy(['type' => 'personnalisable']);
        
        $statuts = [
            CommandeStatut::EN_ATTENTE,
            CommandeStatut::CONFIRMEE,
            CommandeStatut::EN_PREPARATION,
            CommandeStatut::EXPEDIEE,
            CommandeStatut::LIVREE,
        ];
        
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
            
            // Ajouter soit des produits, soit des box, ou les deux
            $type = $faker->numberBetween(1, 4); // 4 = box personnalisable
            
            if ($type == 1 || $type == 3) {
                // Ajouter 1 à 4 produits aléatoires via LigneCommande
                $nbProduits = $faker->numberBetween(1, 4);
                $produitsAleatoires = $faker->randomElements($produits, min($nbProduits, count($produits)));
                
                foreach ($produitsAleatoires as $produit) {
                    $ligne = new LigneCommande();
                    $ligne->setCommande($commande);
                    $ligne->setProduit($produit);
                    $ligne->setQuantite($faker->numberBetween(1, 5));
                    $ligne->setPrixUnitaire($produit->getPrix());
                    
                    $commande->addLigneCommande($ligne);
                    $manager->persist($ligne);
                }
            }
            
            if ($type == 2 || $type == 3) {
                // Ajouter 1 à 2 box fixes aléatoires via LigneCommande
                $boxesFixes = array_filter($boxes, fn($b) => $b->getType() === 'fixe');
                if (!empty($boxesFixes)) {
                    $nbBoxes = $faker->numberBetween(1, 2);
                    $boxesAleatoires = $faker->randomElements($boxesFixes, min($nbBoxes, count($boxesFixes)));
                    
                    foreach ($boxesAleatoires as $box) {
                        $ligne = new LigneCommande();
                        $ligne->setCommande($commande);
                        $ligne->setBox($box);
                        $ligne->setQuantite($faker->numberBetween(1, 3));
                        $ligne->setPrixUnitaire($box->getPrix());
                        
                        $commande->addLigneCommande($ligne);
                        $manager->persist($ligne);
                    }
                }
            }
            
            if ($type == 4 && $boxPersonnalisable) {
                // Ajouter une box personnalisable avec 12 cookies
                $ligne = new LigneCommande();
                $ligne->setCommande($commande);
                $ligne->setBox($boxPersonnalisable);
                $ligne->setQuantite(1);
                $ligne->setPrixUnitaire($boxPersonnalisable->getPrix());
                
                // Sélectionner aléatoirement 12 cookies (peut avoir des doublons)
                $cookiesRestants = 12;
                $cookiesUtilises = [];
                
                while ($cookiesRestants > 0) {
                    $produit = $faker->randomElement($produits);
                    $quantite = $faker->numberBetween(1, min(4, $cookiesRestants));
                    
                    $composition = new CompositionBoxPersonnalisable();
                    $composition->setLigneCommande($ligne);
                    $composition->setProduit($produit);
                    $composition->setQuantite($quantite);
                    
                    $ligne->addCompositionBox($composition);
                    $manager->persist($composition);
                    
                    $cookiesRestants -= $quantite;
                }
                
                $commande->addLigneCommande($ligne);
                $manager->persist($ligne);
            }
            
            // Calculer le total automatiquement
            $commande->setTotalTTC($commande->calculerTotal());
            
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