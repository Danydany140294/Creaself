<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260105212822 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE commande (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, numero_commande VARCHAR(50) NOT NULL, date_commande DATETIME NOT NULL, statut VARCHAR(50) NOT NULL, total_ttc DOUBLE PRECISION NOT NULL, INDEX IDX_6EEAA67DA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE commande_produit (commande_id INT NOT NULL, produit_id INT NOT NULL, INDEX IDX_DF1E9E8782EA2E54 (commande_id), INDEX IDX_DF1E9E87F347EFB (produit_id), PRIMARY KEY(commande_id, produit_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE commande_box (commande_id INT NOT NULL, box_id INT NOT NULL, INDEX IDX_FF95F6A82EA2E54 (commande_id), INDEX IDX_FF95F6AD8177B3F (box_id), PRIMARY KEY(commande_id, box_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67DA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commande_produit ADD CONSTRAINT FK_DF1E9E8782EA2E54 FOREIGN KEY (commande_id) REFERENCES commande (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commande_produit ADD CONSTRAINT FK_DF1E9E87F347EFB FOREIGN KEY (produit_id) REFERENCES produit (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commande_box ADD CONSTRAINT FK_FF95F6A82EA2E54 FOREIGN KEY (commande_id) REFERENCES commande (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commande_box ADD CONSTRAINT FK_FF95F6AD8177B3F FOREIGN KEY (box_id) REFERENCES box (id) ON DELETE CASCADE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67DA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commande_produit DROP FOREIGN KEY FK_DF1E9E8782EA2E54
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commande_produit DROP FOREIGN KEY FK_DF1E9E87F347EFB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commande_box DROP FOREIGN KEY FK_FF95F6A82EA2E54
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commande_box DROP FOREIGN KEY FK_FF95F6AD8177B3F
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE commande
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE commande_produit
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE commande_box
        SQL);
    }
}
