<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211201192649 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création des tables "Card" et "Set"';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE card (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, set_id INTEGER NOT NULL, name VARCHAR(255) NOT NULL, image_path VARCHAR(255) DEFAULT NULL, type_line VARCHAR(255) NOT NULL, color_identity CLOB NOT NULL --(DC2Type:array)
        , oracle_text VARCHAR(255) DEFAULT NULL, artist VARCHAR(255) DEFAULT NULL)');
        $this->addSql('CREATE INDEX IDX_161498D310FB0D18 ON card (set_id)');
        $this->addSql('CREATE TABLE "set" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, code VARCHAR(3) NOT NULL, name VARCHAR(255) NOT NULL, released_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , icon_uri VARCHAR(255) NOT NULL)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE card');
        $this->addSql('DROP TABLE "set"');
    }
}
