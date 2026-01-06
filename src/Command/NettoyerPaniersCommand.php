<?php

namespace App\Command;

use App\Service\PanierService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:nettoyer-paniers',
    description: 'Supprime les paniers expirés (plus de 30 minutes d\'inactivité)',
)]
class NettoyerPaniersCommand extends Command
{
    public function __construct(
        private PanierService $panierService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('🧹 Nettoyage des paniers expirés');

        try {
            $nombrePaniersSupprimes = $this->panierService->nettoyerPaniersExpires();

            if ($nombrePaniersSupprimes > 0) {
                $io->success("✅ {$nombrePaniersSupprimes} panier(s) expiré(s) supprimé(s)");
            } else {
                $io->info("ℹ️  Aucun panier expiré à supprimer");
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $io->error("❌ Erreur lors du nettoyage : " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}