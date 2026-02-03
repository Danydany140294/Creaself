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

        // ========================================
        // CRÉATION DE 15 UTILISATEURS STANDARDS
        // ========================================
        for ($i = 0; $i < 15; $i++) {
            $user = new User();
            $user->setEmail($faker->email);
            $user->setNom($faker->lastName);
            $user->setPrenom($faker->firstName);
            $user->setTelephone($faker->numerify('06########'));
            $user->setRoles(['ROLE_USER']);
            
            // ⭐ NOUVEAUX CHAMPS
            $user->setPointsFidelite($faker->numberBetween(0, 2000)); // Points aléatoires
            $user->setDateNaissance($faker->dateTimeBetween('-60 years', '-18 years')); // Age entre 18 et 60 ans
            
            // Avatar optionnel (50% de chance d'en avoir un)
            if ($faker->boolean(50)) {
                $user->setAvatar('avatar' . $faker->numberBetween(1, 10) . '.jpg');
            }

            // Mot de passe "test1234"
            $user->setPassword(
                $this->passwordHasher->hashPassword($user, 'test1234')
            );

            $manager->persist($user);
            
            // ⭐ Référence pour utiliser dans d'autres fixtures
            $this->addReference('user_' . $i, $user);
        }

        // ========================================
        // CRÉATION DE L'ADMIN
        // ========================================
        $admin = new User();
        $admin->setEmail('admin@creaself.com');
        $admin->setNom('Admin');
        $admin->setPrenom('Super');
        $admin->setTelephone('0601010101');
        $admin->setRoles(['ROLE_ADMIN']);
        
        // ⭐ NOUVEAUX CHAMPS
        $admin->setPointsFidelite(5000); // Admin avec beaucoup de points
        $admin->setDateNaissance(new \DateTime('1990-01-01'));
        $admin->setAvatar('admin-avatar.jpg');

        $admin->setPassword(
            $this->passwordHasher->hashPassword($admin, 'admin1234')
        );

        $manager->persist($admin);
        
        // ⭐ Référence pour l'admin
        $this->addReference('user_admin', $admin);

        $manager->flush();
    }
}