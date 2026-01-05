<?php

namespace App\DataFixtures;

use App\Entity\Produit;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ProduitFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Cookie 1 : Pistache Cannelle
        $cookie1 = new Produit();
        $cookie1->setName('Cookie Pistache Cannelle');
        $cookie1->setDescription('Un délicieux cookie aux éclats de pistache et une touche de cannelle pour une saveur raffinée et épicée');
        $cookie1->setPrix($faker->randomFloat(2, 3.5, 4.5));
        $cookie1->setStock($faker->numberBetween(20, 80));
        $cookie1->setImage('cookie-pistache-cannelle.jpg');
        $cookie1->setDisponible(true);
        $manager->persist($cookie1);

        // Cookie 2 : Pêche Caramel
        $cookie2 = new Produit();
        $cookie2->setName('Cookie Pêche Caramel');
        $cookie2->setDescription('Un cookie gourmand aux morceaux de pêche fondante et caramel onctueux, parfait pour les amateurs de saveurs fruitées');
        $cookie2->setPrix($faker->randomFloat(2, 3.5, 4.5));
        $cookie2->setStock($faker->numberBetween(20, 80));
        $cookie2->setImage('cookie-peche-caramel.jpg');
        $cookie2->setDisponible(true);
        $manager->persist($cookie2);

        // Cookie 3 : Noisette Vanille
        $cookie3 = new Produit();
        $cookie3->setName('Cookie Noisette Vanille');
        $cookie3->setDescription('Un cookie classique et réconfortant aux éclats de noisettes torréfiées et une douce vanille bourbon');
        $cookie3->setPrix($faker->randomFloat(2, 3.5, 4.5));
        $cookie3->setStock($faker->numberBetween(20, 80));
        $cookie3->setImage('cookie-noisette-vanille.jpg');
        $cookie3->setDisponible(true);
        $manager->persist($cookie3);

        // Cookie 4 : Macadamia Framboise
        $cookie4 = new Produit();
        $cookie4->setName('Cookie Macadamia Framboise');
        $cookie4->setDescription('L\'alliance parfaite entre le croquant des noix de macadamia et l\'acidité des framboises séchées');
        $cookie4->setPrix($faker->randomFloat(2, 4.0, 5.0));
        $cookie4->setStock($faker->numberBetween(20, 80));
        $cookie4->setImage('cookie-macadamia-framboise.jpg');
        $cookie4->setDisponible(true);
        $manager->persist($cookie4);

        // Cookie 5 : Fruits Rouges
        $cookie5 = new Produit();
        $cookie5->setName('Cookie Fruits Rouges');
        $cookie5->setDescription('Un cookie aux notes acidulées avec un mélange généreux de fraises, framboises et myrtilles séchées');
        $cookie5->setPrix($faker->randomFloat(2, 3.5, 4.5));
        $cookie5->setStock($faker->numberBetween(20, 80));
        $cookie5->setImage('cookie-fruits-rouges.jpg');
        $cookie5->setDisponible(true);
        $manager->persist($cookie5);

        $manager->flush();
    }
}