<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210310202937 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE card (id INT AUTO_INCREMENT NOT NULL, set_id INT NOT NULL, name VARCHAR(255) NOT NULL, image_url VARCHAR(255) NOT NULL, artist VARCHAR(100) DEFAULT NULL, description LONGTEXT NOT NULL, uuid VARCHAR(100) NOT NULL, INDEX IDX_161498D310FB0D18 (set_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE card_color (card_id INT NOT NULL, color_id INT NOT NULL, INDEX IDX_5E62B92F4ACC9A20 (card_id), INDEX IDX_5E62B92F7ADA1FB5 (color_id), PRIMARY KEY(card_id, color_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE card_type (card_id INT NOT NULL, type_id INT NOT NULL, INDEX IDX_60ED558B4ACC9A20 (card_id), INDEX IDX_60ED558BC54C8C93 (type_id), PRIMARY KEY(card_id, type_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE color (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(1) NOT NULL, name VARCHAR(10) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `set` (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(5) NOT NULL, released_at DATETIME NOT NULL, name VARCHAR(255) NOT NULL, icon_svg_url VARCHAR(255) NOT NULL, uuid VARCHAR(100) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(150) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE card ADD CONSTRAINT FK_161498D310FB0D18 FOREIGN KEY (set_id) REFERENCES `set` (id)');
        $this->addSql('ALTER TABLE card_color ADD CONSTRAINT FK_5E62B92F4ACC9A20 FOREIGN KEY (card_id) REFERENCES card (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE card_color ADD CONSTRAINT FK_5E62B92F7ADA1FB5 FOREIGN KEY (color_id) REFERENCES color (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE card_type ADD CONSTRAINT FK_60ED558B4ACC9A20 FOREIGN KEY (card_id) REFERENCES card (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE card_type ADD CONSTRAINT FK_60ED558BC54C8C93 FOREIGN KEY (type_id) REFERENCES type (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE card_color DROP FOREIGN KEY FK_5E62B92F4ACC9A20');
        $this->addSql('ALTER TABLE card_type DROP FOREIGN KEY FK_60ED558B4ACC9A20');
        $this->addSql('ALTER TABLE card_color DROP FOREIGN KEY FK_5E62B92F7ADA1FB5');
        $this->addSql('ALTER TABLE card DROP FOREIGN KEY FK_161498D310FB0D18');
        $this->addSql('ALTER TABLE card_type DROP FOREIGN KEY FK_60ED558BC54C8C93');
        $this->addSql('DROP TABLE card');
        $this->addSql('DROP TABLE card_color');
        $this->addSql('DROP TABLE card_type');
        $this->addSql('DROP TABLE color');
        $this->addSql('DROP TABLE `set`');
        $this->addSql('DROP TABLE type');
    }
}
