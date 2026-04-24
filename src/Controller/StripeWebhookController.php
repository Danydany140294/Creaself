<?php

namespace App\Controller;

use App\Entity\Box;
use App\Entity\Commande;
use App\Entity\LigneCommande;
use App\Entity\CompositionBoxPersonnalisable;
use App\Entity\Produit;
use App\Enum\CommandeStatut;
use App\Repository\CommandeRepository;
use App\Repository\PanierRepository;
use App\Repository\LignePanierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

final class StripeWebhookController extends AbstractController
{
    public function __construct(
        private MailerInterface $mailer,
        private Environment $twig
    ) {}

    #[Route('/webhook/stripe', name: 'app_webhook_stripe', methods: ['POST'])]
    public function handleWebhook(
        Request $request,
        EntityManagerInterface $em,
        CommandeRepository $commandeRepo,
        PanierRepository $panierRepo,
        LignePanierRepository $lignePanierRepo
    ): JsonResponse
    {
        $webhookSecret = $_ENV['STRIPE_WEBHOOK_SECRET'] ?? '';
        $payload = $request->getContent();
        $sigHeader = $request->headers->get('stripe-signature');

        // ✅ Vérifier la signature Stripe
        try {
            \Stripe\Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY'] ?? '');
            $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Signature invalide'], 400);
        }

        // ✅ Session expirée — paiement abandonné
        if ($event->type === 'checkout.session.expired') {
            return new JsonResponse(['status' => 'session_expired']);
        }

        // ✅ On traite uniquement le paiement confirmé
        if ($event->type !== 'checkout.session.completed') {
            return new JsonResponse(['status' => 'ignored']);
        }

        $stripeSession = $event->data->object;

        // ✅ Éviter les doublons (si webhook reçu deux fois)
        $existing = $commandeRepo->findOneBy(['stripeSessionId' => $stripeSession->id]);
        if ($existing) {
            return new JsonResponse(['status' => 'already_processed']);
        }

        // ✅ Vérifier que le paiement est bien payé
        if ($stripeSession->payment_status !== 'paid') {
            return new JsonResponse(['status' => 'not_paid']);
        }

        // ✅ Créer la commande
        $commande = new Commande();
        $commande->setStripeSessionId($stripeSession->id);
        $commande->setNumeroCommande('CMD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6)));
        $commande->setDateCommande(new \DateTime());
        $commande->setStatut(CommandeStatut::PAYEE);
        $commande->setTotalTTC($stripeSession->amount_total / 100);

        // ✅ Retrouver le user via l'email Stripe
        $stripeEmail = $stripeSession->customer_email;
        if ($stripeEmail) {
            $user = $em->getRepository(\App\Entity\User::class)->findOneBy(['email' => $stripeEmail]);
            if ($user) {
                $commande->setUser($user);

                // ✅ Récupérer et vider le panier BDD
                $panier = $panierRepo->findOneBy(['user' => $user]);
                if ($panier) {
                    $lignesPanier = $lignePanierRepo->findBy(['panier' => $panier]);

                    foreach ($lignesPanier as $lignePanier) {
                        $ligneCommande = new LigneCommande();
                        $ligneCommande->setCommande($commande);
                        $ligneCommande->setPrixUnitaire($lignePanier->getPrixUnitaire());
                        $ligneCommande->setQuantite($lignePanier->getQuantite());

                        if ($lignePanier->getProduit()) {
                            $ligneCommande->setProduit($lignePanier->getProduit());
                            // ✅ Décrémenter le stock du produit
                            $lignePanier->getProduit()->decrémenterStock($lignePanier->getQuantite());
                        }

                        if ($lignePanier->getBox()) {
                            $ligneCommande->setBox($lignePanier->getBox());
                        }

                        // ✅ Compositions box perso
                        if ($lignePanier->isBoxPersonnalisable()) {
                            foreach ($lignePanier->getCompositionsPanier() as $compo) {
                                $compoBox = new CompositionBoxPersonnalisable();
                                $compoBox->setProduit($compo->getProduit());
                                $compoBox->setQuantite($compo->getQuantite());
                                $compoBox->setLigneCommande($ligneCommande);
                                $em->persist($compoBox);
                                $ligneCommande->addCompositionBox($compoBox);
                            }
                        }

                        $em->persist($ligneCommande);
                    }

                    // ✅ Vider le panier
                    foreach ($lignesPanier as $ligne) {
                        $em->remove($ligne);
                    }
                    $em->remove($panier);
                }
            }
        }

        $em->persist($commande);
        $em->flush();

        // ✅ Envoyer l'email de confirmation
        if ($commande->getUser()) {
            try {
                $html = $this->twig->render('emails/confirmation_commande.html.twig', [
                    'commande' => $commande
                ]);

                $emailMessage = (new Email())
                    ->from('noreply@creaself.fr')
                    ->to($commande->getUser()->getEmail())
                    ->subject('Confirmation de votre commande ' . $commande->getNumeroCommande())
                    ->html($html);

                $this->mailer->send($emailMessage);
            } catch (\Exception $e) {
                // Silencieux — ne pas bloquer la commande si l'email échoue
            }
        }

        return new JsonResponse(['status' => 'success']);
    }
}