<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260108224652 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute la box template personnalisable (25€, 12 cookies)';
    }

    public function up(Schema $schema): void
    {
        // Insérer la box template personnalisable
        $this->addSql("
            INSERT INTO box (nom, prix, description, image, stock, type, createur_id, date_creation) 
            VALUES (
                'Box Personnalisable', 
                25.00, 
                'Composez votre propre box avec 12 cookies de votre choix !', 
                'box-perso.jpg', 
                9999, 
                'personnalisable',
                NULL,
                NULL
            )
        ");
    }

    public function down(Schema $schema): void
    {
        // Supprimer la box template lors d'un rollback
        $this->addSql("
            DELETE FROM box 
            WHERE type = 'personnalisable' 
            AND createur_id IS NULL 
            AND nom = 'Box Personnalisable'
        ");
    }
}