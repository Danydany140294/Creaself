<?php

namespace App\Service;

use App\Entity\Box;
use App\Entity\CompositionPanierPersonnalisable;
use App\Entity\LignePanier;
use App\Entity\Panier;
use App\Entity\Produit;
use App\Entity\User;
use App\Repository\PanierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

class PanierService
{
    private const SESSION_KEY = 'panier';

    /** Tailles autorisées */
    private const TAILLES_BOX_PERSO = [6, 12, 24];

    /** Prix fixes par taille */
    private const PRIX_BOX_PERSO = [
        6  => 12.00,
        12 => 24.00,
        24 => 30.00,
    ];

    public function __construct(
        private EntityManagerInterface $entityManager,
        private PanierRepository $panierRepository,
        private RequestStack $requestStack,
        private Security $security
    ) {}

    // ======================================================
    // PANIER GLOBAL
    // ======================================================

    public function getPanier(): array
    {
        $user = $this->security->getUser();

        return $user instanceof User
            ? $this->getPanierBDD($user)
            : $this->getPanierSession();
    }

    private function getPanierSession(): array
    {
        $session = $this->requestStack->getSession();

        $panier = $session->get(self::SESSION_KEY, [
            'produits'        => [],
            'boxes'           => [],
            'boxes_perso'     => [],
            'date_expiration' => new \DateTime('+10 minutes'),
        ]);

        if ($panier['date_expiration'] < new \DateTime()) {
            $this->viderPanierSession();

            return [
                'produits'        => [],
                'boxes'           => [],
                'boxes_perso'     => [],
                'date_expiration' => new \DateTime('+10 minutes'),
            ];
        }

        return $panier;
    }

    private function getPanierBDD(User $user): array
    {
        $panier = $this->panierRepository->findByUser($user);

        if (!$panier) {
            $panier = new Panier();
            $panier->setUser($user);
            $this->entityManager->persist($panier);
            $this->entityManager->flush();
        }

        if ($panier->isExpire()) {
            $this->viderPanierBDD($panier);
        } else {
            $panier->rafraichirExpiration();
            $this->entityManager->flush();
        }

        $lignes = [];

        foreach ($panier->getLignesPanier() as $ligne) {

            $data = [
                'id'            => $ligne->getId(),
                'quantite'      => $ligne->getQuantite(),
                'prix_unitaire' => $ligne->getPrixUnitaire(),
                'sous_total'    => $ligne->getSousTotal(),
                'is_box_perso'  => $ligne->isBoxPersonnalisable(),
                'taille_box'    => $ligne->getTailleBox(),
            ];

            if ($ligne->getProduit()) {
                $data['produit'] = [
                    'id'    => $ligne->getProduit()->getId(),
                    'name'  => $ligne->getProduit()->getName(),
                    'prix'  => $ligne->getProduit()->getPrix(),
                ];
            }

            if ($ligne->getBox()) {
                $data['box'] = [
                    'id'   => $ligne->getBox()->getId(),
                    'nom'  => $ligne->getBox()->getNom(),
                    'prix' => $ligne->getBox()->getPrix(),
                ];
            }

            if ($ligne->isBoxPersonnalisable()) {
                $compo = [];

                foreach ($ligne->getCompositionsPanier() as $c) {
                    $compo[] = [
                        'produit' => [
                            'id'   => $c->getProduit()->getId(),
                            'name' => $c->getProduit()->getName(),
                            'prix' => $c->getProduit()->getPrix(),
                        ],
                        'quantite' => $c->getQuantite(),
                    ];
                }

                $data['compositions'] = $compo;
            }

            $lignes[] = $data;
        }

        return [
            'panier_id' => $panier->getId(),
            'lignes'    => $lignes,
            'total'     => $panier->getTotal(),
        ];
    }

    // ======================================================
    // PRODUITS
    // ======================================================

    public function ajouterProduit(Produit $produit, int $quantite = 1): void
    {
        if ($quantite <= 0) {
            throw new \Exception("Quantité invalide");
        }

        if (!$produit->isDisponible()) {
            throw new \Exception("Produit indisponible");
        }

        if ($produit->getStock() < $quantite) {
            throw new \Exception("Stock insuffisant");
        }

        $user = $this->security->getUser();

        $user instanceof User
            ? $this->ajouterProduitBDD($user, $produit, $quantite)
            : $this->ajouterProduitSession($produit, $quantite);
    }

    private function ajouterProduitSession(Produit $produit, int $quantite): void
    {
        $session = $this->requestStack->getSession();
        $panier  = $this->getPanierSession();

        $id = $produit->getId();

        $panier['produits'][$id]['quantite'] =
            ($panier['produits'][$id]['quantite'] ?? 0) + $quantite;

        $panier['produits'][$id]['produit'] = $produit;

        $panier['date_expiration'] = new \DateTime('+10 minutes');

        $session->set(self::SESSION_KEY, $panier);
    }

    private function ajouterProduitBDD(User $user, Produit $produit, int $quantite): void
    {
        $panier = $this->panierRepository->findByUser($user);

        if (!$panier) {
            $panier = new Panier();
            $panier->setUser($user);
            $this->entityManager->persist($panier);
        }

        foreach ($panier->getLignesPanier() as $ligne) {
            if ($ligne->getProduit() && $ligne->getProduit() === $produit) {
                $ligne->setQuantite($ligne->getQuantite() + $quantite);
                $this->entityManager->flush();
                return;
            }
        }

        $ligne = new LignePanier();
        $ligne->setPanier($panier);
        $ligne->setProduit($produit);
        $ligne->setQuantite($quantite);

        $this->entityManager->persist($ligne);
        $this->entityManager->flush();
    }


    public function ajouterBox(Box $box, int $quantite = 1): void
{
    $user = $this->security->getUser();

    $user instanceof User
        ? $this->ajouterBoxBDD($user, $box, $quantite)
        : $this->ajouterBoxSession($box, $quantite);
}

private function ajouterBoxSession(Box $box, int $quantite): void
{
    $session = $this->requestStack->getSession();
    $panier  = $this->getPanierSession();

    $id = $box->getId();

    $panier['boxes'][$id]['quantite'] =
        ($panier['boxes'][$id]['quantite'] ?? 0) + $quantite;

    $panier['boxes'][$id]['box'] = $box;
    $panier['date_expiration']   = new \DateTime('+10 minutes');

    $session->set(self::SESSION_KEY, $panier);
}

private function ajouterBoxBDD(User $user, Box $box, int $quantite): void
{
    $panier = $this->panierRepository->findByUser($user);

    if (!$panier) {
        $panier = new Panier();
        $panier->setUser($user);
        $this->entityManager->persist($panier);
    }

    foreach ($panier->getLignesPanier() as $ligne) {
        if ($ligne->getBox() && $ligne->getBox() === $box && !$ligne->isBoxPersonnalisable()) {
            $ligne->setQuantite($ligne->getQuantite() + $quantite);
            $this->entityManager->flush();
            return;
        }
    }

    $ligne = new LignePanier();
    $ligne->setPanier($panier);
    $ligne->setBox($box);
    $ligne->setQuantite($quantite);

    $this->entityManager->persist($ligne);
    $this->entityManager->flush();
}
    // ======================================================
    // BOX PERSONNALISÉE
    // ======================================================

    public function ajouterBoxPersonnalisable(
        Box $box,
        array $cookies,
        int $taille
    ): void {

        if (!in_array($taille, self::TAILLES_BOX_PERSO)) {
            throw new \Exception("Taille invalide");
        }

        if (array_sum($cookies) !== $taille) {
            throw new \Exception("La box doit contenir {$taille} cookies");
        }

        $prix = self::PRIX_BOX_PERSO[$taille] ?? $box->getPrix();

        $user = $this->security->getUser();

        $user instanceof User
            ? $this->ajouterBoxPersoBDD($user, $box, $cookies, $taille, $prix)
            : $this->ajouterBoxPersoSession($box, $cookies, $taille, $prix);
    }

    private function ajouterBoxPersoSession(Box $box, array $cookies, int $taille, float $prix): void
    {
        $session = $this->requestStack->getSession();
        $panier  = $this->getPanierSession();

        $panier['boxes_perso'][] = [
            'box'      => $box,
            'cookies'  => $cookies,
            'taille'   => $taille,
            'prix'     => $prix,
        ];

        $panier['date_expiration'] = new \DateTime('+10 minutes');

        $session->set(self::SESSION_KEY, $panier);
    }

    private function ajouterBoxPersoBDD(User $user, Box $box, array $cookies, int $taille, float $prix): void
    {
        $panier = $this->panierRepository->findByUser($user);

        if (!$panier) {
            $panier = new Panier();
            $panier->setUser($user);
            $this->entityManager->persist($panier);
        }

        $ligne = new LignePanier();
        $ligne->setPanier($panier);
        $ligne->setBox($box);
        $ligne->setQuantite(1);
        $ligne->setTailleBox($taille);

        foreach ($cookies as $id => $qte) {
            $produit = $this->entityManager->getRepository(Produit::class)->find($id);

            if (!$produit) continue;

            $compo = new CompositionPanierPersonnalisable();
            $compo->setLignePanier($ligne);
            $compo->setProduit($produit);
            $compo->setQuantite($qte);

            $ligne->addCompositionPanier($compo);
            $this->entityManager->persist($compo);
        }

        $this->entityManager->persist($ligne);
        $this->entityManager->flush();
    }

    public function migrerSessionVersBDD(User $user): void
{
    $session = $this->requestStack->getSession();
    $panierSession = $session->get(self::SESSION_KEY);

    if (!$panierSession) {
        return;
    }

    $panier = $this->panierRepository->findByUser($user);

    if (!$panier) {
        $panier = new Panier();
        $panier->setUser($user);
        $this->entityManager->persist($panier);
    }

    // Migrer les produits simples
    foreach ($panierSession['produits'] ?? [] as $id => $item) {
        $produit = $this->entityManager->getRepository(Produit::class)->find($id);
        if ($produit) {
            $this->ajouterProduitBDD($user, $produit, $item['quantite']);
        }
    }

    // Migrer les boxes simples
    foreach ($panierSession['boxes'] ?? [] as $id => $item) {
    $box = $this->entityManager->getRepository(Box::class)->find($id);
    if ($box) {
        $this->ajouterBoxBDD($user, $box, $item['quantite']);
    }
}

    // Migrer les boxes personnalisées
    foreach ($panierSession['boxes_perso'] ?? [] as $item) {
    $box = $this->entityManager->getRepository(Box::class)->find($item['box']->getId());
    if (!$box) continue;
    $this->ajouterBoxPersoBDD($user, $box, $item['cookies'], $item['taille'], $item['prix']);
}

    // Vider la session après migration
    $this->viderPanierSession();
}

    // ======================================================
    // UTILITAIRES
    // ======================================================

    private function viderPanierSession(): void
    {
        $this->requestStack->getSession()->remove(self::SESSION_KEY);
    }

    private function viderPanierBDD(Panier $panier): void
    {
        foreach ($panier->getLignesPanier() as $ligne) {
            $this->entityManager->remove($ligne);
        }

        $this->entityManager->flush();
    }
}