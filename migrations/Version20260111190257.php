<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260111190257 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE box ADD createur_id INT DEFAULT NULL, ADD date_creation DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE box ADD CONSTRAINT FK_8A9483A73A201E5 FOREIGN KEY (createur_id) REFERENCES `user` (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_8A9483A73A201E5 ON box (createur_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE box DROP FOREIGN KEY FK_8A9483A73A201E5
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_8A9483A73A201E5 ON box
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE box DROP createur_id, DROP date_creation
        SQL);
    }
}
