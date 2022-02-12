<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220212093436 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add entity Color and relation ManyToMany between Card and Color';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE cards_colors (card_id CHAR(36) NOT NULL --(DC2Type:uuid)
        , color_abbreviation VARCHAR(255) NOT NULL, PRIMARY KEY(card_id, color_abbreviation))');
        $this->addSql('CREATE INDEX IDX_C24ED9734ACC9A20 ON cards_colors (card_id)');
        $this->addSql('CREATE INDEX IDX_C24ED973B539744B ON cards_colors (color_abbreviation)');
        $this->addSql('CREATE TABLE color (abbreviation VARCHAR(255) NOT NULL, name VARCHAR(50) NOT NULL, basic_land VARCHAR(50) NOT NULL, PRIMARY KEY(abbreviation))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_665648E95E237E06 ON color (name)');
        $this->addSql('DROP INDEX IDX_161498D310FB0D18');
        $this->addSql('CREATE TEMPORARY TABLE __temp__card AS SELECT id, set_id, name, image_url, type, description, artist FROM card');
        $this->addSql('DROP TABLE card');
        $this->addSql('CREATE TABLE card (id CHAR(36) NOT NULL COLLATE BINARY --(DC2Type:uuid)
        , set_id CHAR(36) NOT NULL COLLATE BINARY --(DC2Type:uuid)
        , name VARCHAR(255) NOT NULL COLLATE BINARY, type VARCHAR(255) DEFAULT NULL COLLATE BINARY, description CLOB DEFAULT NULL COLLATE BINARY, artist VARCHAR(255) DEFAULT NULL COLLATE BINARY, image_url VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id), CONSTRAINT FK_161498D310FB0D18 FOREIGN KEY (set_id) REFERENCES "set" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO card (id, set_id, name, image_url, type, description, artist) SELECT id, set_id, name, image_url, type, description, artist FROM __temp__card');
        $this->addSql('DROP TABLE __temp__card');
        $this->addSql('CREATE INDEX IDX_161498D310FB0D18 ON card (set_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__set AS SELECT id, code, name, released_at, icon_url FROM "set"');
        $this->addSql('DROP TABLE "set"');
        $this->addSql('CREATE TABLE "set" (id CHAR(36) NOT NULL COLLATE BINARY --(DC2Type:uuid)
        , name VARCHAR(255) NOT NULL COLLATE BINARY, icon_url VARCHAR(255) NOT NULL COLLATE BINARY, code VARCHAR(5) NOT NULL, released_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , PRIMARY KEY(id))');
        $this->addSql('INSERT INTO "set" (id, code, name, released_at, icon_url) SELECT id, code, name, released_at, icon_url FROM __temp__set');
        $this->addSql('DROP TABLE __temp__set');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE cards_colors');
        $this->addSql('DROP TABLE color');
        $this->addSql('DROP INDEX IDX_161498D310FB0D18');
        $this->addSql('CREATE TEMPORARY TABLE __temp__card AS SELECT id, set_id, name, image_url, type, description, artist FROM card');
        $this->addSql('DROP TABLE card');
        $this->addSql('CREATE TABLE card (id CHAR(36) NOT NULL --(DC2Type:uuid)
        , set_id CHAR(36) NOT NULL --(DC2Type:uuid)
        , name VARCHAR(255) NOT NULL, type VARCHAR(255) DEFAULT NULL, description CLOB DEFAULT NULL, artist VARCHAR(255) DEFAULT NULL, image_url VARCHAR(255) NOT NULL COLLATE BINARY, colors CLOB NOT NULL COLLATE BINARY --(DC2Type:array)
        , PRIMARY KEY(id))');
        $this->addSql('INSERT INTO card (id, set_id, name, image_url, type, description, artist) SELECT id, set_id, name, image_url, type, description, artist FROM __temp__card');
        $this->addSql('DROP TABLE __temp__card');
        $this->addSql('CREATE INDEX IDX_161498D310FB0D18 ON card (set_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__set AS SELECT id, code, name, released_at, icon_url FROM "set"');
        $this->addSql('DROP TABLE "set"');
        $this->addSql('CREATE TABLE "set" (id CHAR(36) NOT NULL --(DC2Type:uuid)
        , name VARCHAR(255) NOT NULL, icon_url VARCHAR(255) NOT NULL, code VARCHAR(3) NOT NULL COLLATE BINARY, released_at DATETIME DEFAULT \'NULL --(DC2Type:datetime_immutable)\' --(DC2Type:datetime_immutable)
        , PRIMARY KEY(id))');
        $this->addSql('INSERT INTO "set" (id, code, name, released_at, icon_url) SELECT id, code, name, released_at, icon_url FROM __temp__set');
        $this->addSql('DROP TABLE __temp__set');
    }
}
