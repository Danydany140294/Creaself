<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260105211639 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE box_produit (box_id INT NOT NULL, produit_id INT NOT NULL, INDEX IDX_30677216D8177B3F (box_id), INDEX IDX_30677216F347EFB (produit_id), PRIMARY KEY(box_id, produit_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE box_produit ADD CONSTRAINT FK_30677216D8177B3F FOREIGN KEY (box_id) REFERENCES box (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE box_produit ADD CONSTRAINT FK_30677216F347EFB FOREIGN KEY (produit_id) REFERENCES produit (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE box ADD type VARCHAR(50) NOT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE box_produit DROP FOREIGN KEY FK_30677216D8177B3F
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE box_produit DROP FOREIGN KEY FK_30677216F347EFB
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE box_produit
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE box DROP type
        SQL);
    }
}
