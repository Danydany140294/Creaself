<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260106205650 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE composition_panier_personnalisable (id INT AUTO_INCREMENT NOT NULL, ligne_panier_id INT NOT NULL, produit_id INT NOT NULL, quantite INT NOT NULL, INDEX IDX_B42582D838989DF4 (ligne_panier_id), INDEX IDX_B42582D8F347EFB (produit_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE ligne_panier (id INT AUTO_INCREMENT NOT NULL, panier_id INT NOT NULL, produit_id INT DEFAULT NULL, box_id INT DEFAULT NULL, quantite INT NOT NULL, INDEX IDX_21691B4F77D927C (panier_id), INDEX IDX_21691B4F347EFB (produit_id), INDEX IDX_21691B4D8177B3F (box_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE panier (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, date_creation DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', date_expiration DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', UNIQUE INDEX UNIQ_24CC0DF2A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE composition_panier_personnalisable ADD CONSTRAINT FK_B42582D838989DF4 FOREIGN KEY (ligne_panier_id) REFERENCES ligne_panier (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE composition_panier_personnalisable ADD CONSTRAINT FK_B42582D8F347EFB FOREIGN KEY (produit_id) REFERENCES produit (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ligne_panier ADD CONSTRAINT FK_21691B4F77D927C FOREIGN KEY (panier_id) REFERENCES panier (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ligne_panier ADD CONSTRAINT FK_21691B4F347EFB FOREIGN KEY (produit_id) REFERENCES produit (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ligne_panier ADD CONSTRAINT FK_21691B4D8177B3F FOREIGN KEY (box_id) REFERENCES box (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE panier ADD CONSTRAINT FK_24CC0DF2A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE composition_panier_personnalisable DROP FOREIGN KEY FK_B42582D838989DF4
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE composition_panier_personnalisable DROP FOREIGN KEY FK_B42582D8F347EFB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ligne_panier DROP FOREIGN KEY FK_21691B4F77D927C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ligne_panier DROP FOREIGN KEY FK_21691B4F347EFB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ligne_panier DROP FOREIGN KEY FK_21691B4D8177B3F
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE panier DROP FOREIGN KEY FK_24CC0DF2A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE composition_panier_personnalisable
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE ligne_panier
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE panier
        SQL);
    }
}
