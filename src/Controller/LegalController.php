<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class LegalController extends AbstractController
{
    #[Route('/mentions-legales', name: 'app_mentions_legales')]
    public function mentionsLegales(): Response
    {
        return $this->render('legal/mentions_legales.html.twig');
    }

    #[Route('/cgv', name: 'app_cgv')]
    public function cgv(): Response
    {
        return $this->render('legal/cgv.html.twig');
    }

    #[Route('/confidentialite', name: 'app_confidentialite')]
    public function confidentialite(): Response
    {
        return $this->render('legal/confidentialite.html.twig');
    }

    #[Route('/cookies/accept', name: 'app_cookies_accept', methods: ['POST'])]
public function acceptCookies(Request $request): JsonResponse
{
    $request->getSession()->set('cookies_accepted', true);
    return new JsonResponse(['status' => 'ok']);
}

#[Route('/cookies/refuse', name: 'app_cookies_refuse', methods: ['POST'])]
public function refuseCookies(Request $request): JsonResponse
{
    $request->getSession()->set('cookies_accepted', false);
    return new JsonResponse(['status' => 'ok']);
}
}