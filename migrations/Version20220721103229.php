<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220721103229 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création des tables set, color et card.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE card_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE color_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE set_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE card (id INT NOT NULL, set_id INT NOT NULL, name VARCHAR(255) NOT NULL, image VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, artist VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_161498D310FB0D18 ON card (set_id)');
        $this->addSql('CREATE TABLE card_color (card_id INT NOT NULL, color_id INT NOT NULL, PRIMARY KEY(card_id, color_id))');
        $this->addSql('CREATE INDEX IDX_5E62B92F4ACC9A20 ON card_color (card_id)');
        $this->addSql('CREATE INDEX IDX_5E62B92F7ADA1FB5 ON card_color (color_id)');
        $this->addSql('CREATE TABLE color (id INT NOT NULL, abbr VARCHAR(2) NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE set (id INT NOT NULL, code VARCHAR(10) NOT NULL, released_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, name VARCHAR(255) NOT NULL, icon VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN set.released_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE card ADD CONSTRAINT FK_161498D310FB0D18 FOREIGN KEY (set_id) REFERENCES set (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE card_color ADD CONSTRAINT FK_5E62B92F4ACC9A20 FOREIGN KEY (card_id) REFERENCES card (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE card_color ADD CONSTRAINT FK_5E62B92F7ADA1FB5 FOREIGN KEY (color_id) REFERENCES color (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE card_color DROP CONSTRAINT FK_5E62B92F4ACC9A20');
        $this->addSql('ALTER TABLE card_color DROP CONSTRAINT FK_5E62B92F7ADA1FB5');
        $this->addSql('ALTER TABLE card DROP CONSTRAINT FK_161498D310FB0D18');
        $this->addSql('DROP SEQUENCE card_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE color_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE set_id_seq CASCADE');
        $this->addSql('DROP TABLE card');
        $this->addSql('DROP TABLE card_color');
        $this->addSql('DROP TABLE color');
        $this->addSql('DROP TABLE set');
    }
}
