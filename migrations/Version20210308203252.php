<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210308203252 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE card (id INT AUTO_INCREMENT NOT NULL, set_id INT NOT NULL, name VARCHAR(255) NOT NULL, image_url VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, artist VARCHAR(100) DEFAULT NULL, description LONGTEXT NOT NULL, INDEX IDX_161498D310FB0D18 (set_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `set` (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(5) NOT NULL, released_at DATETIME NOT NULL, name VARCHAR(255) NOT NULL, icon_svg_url VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE card ADD CONSTRAINT FK_161498D310FB0D18 FOREIGN KEY (set_id) REFERENCES `set` (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE card DROP FOREIGN KEY FK_161498D310FB0D18');
        $this->addSql('DROP TABLE card');
        $this->addSql('DROP TABLE `set`');
    }
}
