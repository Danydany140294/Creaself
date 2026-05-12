<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Hash le mot de passe
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            // Rôle par défaut
            $user->setRoles(['ROLE_USER']);

            // Avatar optionnel
            $avatarFile = $form->get('avatarFile')->getData();
            if ($avatarFile) {
                $extension = strtolower(pathinfo($avatarFile->getClientOriginalName(), PATHINFO_EXTENSION));
                $nouveauNom = uniqid('avatar_') . '.' . $extension;
                $avatarFile->move(
                    $this->getParameter('kernel.project_dir') . '/public/asset/images/home/',
                    $nouveauNom
                );
                $user->setAvatar($nouveauNom);
            }
            // Si pas de fichier → avatar reste null (pas de photo)

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Votre compte a été créé avec succès !');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}