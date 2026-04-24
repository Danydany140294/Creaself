<?php

namespace App\Controller;

use App\Entity\Adresse;
use App\Form\AdresseType;
use App\Service\AdresseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/compte/adresses')]
#[IsGranted('ROLE_USER')]
class AdresseController extends AbstractController
{
    public function __construct(
        private AdresseService $adresseService
    ) {}

    #[Route('', name: 'app_adresses_index')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        $adresse = new Adresse();
        $adresse->setUser($user);

        $form = $this->createForm(AdresseType::class, $adresse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($adresse->isParDefaut()) {
                $this->adresseService->removeDefaultFromOtherAddresses($user);
            }

            if ($user->getAdresses()->count() === 0) {
                $adresse->setParDefaut(true);
            }

            $adresse->setDateCreation(new \DateTime());

            $em->persist($adresse);
            $em->flush();

            $this->addFlash('success', 'Adresse ajoutée avec succès.');
            return $this->redirectToRoute('app_adresses_index');
        }

        return $this->render('compte/adresses.html.twig', [
            'addressForm' => $form->createView(),
        ]);
    }

    #[Route('/{id}/modifier', name: 'app_adresses_edit', methods: ['GET', 'POST'])]
    public function edit(Adresse $adresse, Request $request, EntityManagerInterface $em): Response
    {
        if ($adresse->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas modifier cette adresse.');
        }

        $form = $this->createForm(AdresseType::class, $adresse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($adresse->isParDefaut()) {
                $this->adresseService->removeDefaultFromOtherAddresses($this->getUser());
                $adresse->setParDefaut(true);
            }

            $em->flush();

            $this->addFlash('success', 'Adresse modifiée avec succès.');
            return $this->redirectToRoute('app_adresses_index');
        }

        return $this->render('compte/adresses.html.twig', [
            'addressForm' => $form->createView(),
            'editMode' => true,
            'adresseToEdit' => $adresse,
        ]);
    }

    #[Route('/{id}/supprimer', name: 'app_adresses_delete', methods: ['DELETE', 'POST', 'GET'])]
    public function delete(Adresse $adresse, Request $request, EntityManagerInterface $em): Response
    {
        if ($adresse->getUser() !== $this->getUser()) {
            if ($request->isXmlHttpRequest()) {
                return $this->json(['success' => false, 'message' => 'Accès refusé'], 403);
            }
            throw $this->createAccessDeniedException('Vous ne pouvez pas supprimer cette adresse.');
        }

        $userAddresses = $this->getUser()->getAdresses();
        if ($adresse->isParDefaut() && count($userAddresses) > 1) {
            $message = 'Vous ne pouvez pas supprimer votre adresse par défaut. Définissez d\'abord une autre adresse comme adresse par défaut.';
            if ($request->isXmlHttpRequest()) {
                return $this->json(['success' => false, 'message' => $message], 400);
            }
            $this->addFlash('warning', $message);
            return $this->redirectToRoute('app_adresses_index');
        }

        $wasDefault = $adresse->isParDefaut();

        $em->remove($adresse);
        $em->flush();

        if ($wasDefault) {
            $autreAdresse = $this->getUser()->getAdresses()->first();
            if ($autreAdresse) {
                $autreAdresse->setParDefaut(true);
                $em->flush();
            }
        }

        $message = 'Adresse supprimée avec succès';

        if ($request->isXmlHttpRequest()) {
            return $this->json(['success' => true, 'message' => $message]);
        }

        $this->addFlash('success', $message);
        return $this->redirectToRoute('app_adresses_index');
    }

    #[Route('/{id}/definir-par-defaut', name: 'app_adresses_set_default', methods: ['POST', 'GET'])]
    public function setDefault(Adresse $adresse, Request $request, EntityManagerInterface $em): Response
    {
        if ($adresse->getUser() !== $this->getUser()) {
            if ($request->isXmlHttpRequest()) {
                return $this->json(['success' => false, 'message' => 'Accès refusé'], 403);
            }
            throw $this->createAccessDeniedException('Vous ne pouvez pas modifier cette adresse.');
        }

        $this->adresseService->removeDefaultFromOtherAddresses($this->getUser());
        $adresse->setParDefaut(true);
        $em->flush();

        $message = 'Adresse définie par défaut';

        if ($request->isXmlHttpRequest()) {
            return $this->json(['success' => true, 'message' => $message]);
        }

        $this->addFlash('success', $message);
        return $this->redirectToRoute('app_adresses_index');
    }
}