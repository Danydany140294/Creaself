<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker\Factory;

class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR'); // Faker français

        for ($i = 0; $i < 15; $i++) {
            $user = new User();
            $user->setEmail($faker->email);
            $user->setNom($faker->lastName);
            $user->setPrénom($faker->firstName);
            $user->setTéléphone($faker->numerify('06########'));
            $user->setRoles(['ROLE_USER']); // rôle utilisateur standard

            // Mot de passe "test1234" encodé
            $user->setPassword(
                $this->passwordHasher->hashPassword($user, 'test1234')
            );

            $manager->persist($user);
        }

        // Création de l'admin
        $admin = new User();
        $admin->setEmail('admin@creaself.com');
        $admin->setNom('Admin');
        $admin->setPrénom('Super');
        $admin->setTéléphone('0601010101');
        $admin->setRoles(['ROLE_ADMIN']); // rôle admin
        $admin->setPassword(
            $this->passwordHasher->hashPassword($admin, 'admin1234')
        );
        

        $manager->persist($admin);

        $manager->flush();
    }
}
