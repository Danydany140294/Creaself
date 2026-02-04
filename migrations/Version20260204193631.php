<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260204193631 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE moyen_paiement (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, type VARCHAR(50) NOT NULL, stripe_payment_method_id VARCHAR(255) DEFAULT NULL, derniers4_chiffres VARCHAR(4) DEFAULT NULL, marque VARCHAR(20) DEFAULT NULL, expiration VARCHAR(7) DEFAULT NULL, nom VARCHAR(100) DEFAULT NULL, par_defaut TINYINT(1) NOT NULL, date_ajout DATETIME NOT NULL, actif TINYINT(1) NOT NULL, INDEX IDX_ED4417D2A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE moyen_paiement ADD CONSTRAINT FK_ED4417D2A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user ADD stripe_customer_id VARCHAR(255) DEFAULT NULL, CHANGE telephone telephone VARCHAR(20) DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE moyen_paiement DROP FOREIGN KEY FK_ED4417D2A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE moyen_paiement
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE `user` DROP stripe_customer_id, CHANGE telephone telephone VARCHAR(20) NOT NULL
        SQL);
    }
}
