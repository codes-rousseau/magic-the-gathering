<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220210174220 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add "Card" and "Set" entities';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE card (id CHAR(36) NOT NULL --(DC2Type:uuid)
        , set_id CHAR(36) NOT NULL --(DC2Type:uuid)
        , name VARCHAR(255) NOT NULL, image_url VARCHAR(255) NOT NULL, type VARCHAR(255) DEFAULT NULL, colors CLOB NOT NULL --(DC2Type:array)
        , description CLOB DEFAULT NULL, artist VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_161498D310FB0D18 ON card (set_id)');
        $this->addSql('CREATE TABLE "set" (id CHAR(36) NOT NULL --(DC2Type:uuid)
        , code VARCHAR(3) NOT NULL, name VARCHAR(255) NOT NULL, released_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , icon_url VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE card');
        $this->addSql('DROP TABLE "set"');
    }
}
