<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220224070948 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE card (id INT AUTO_INCREMENT NOT NULL, color_id INT NOT NULL, type_id INT NOT NULL, name VARCHAR(255) NOT NULL, image VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, artist_name VARCHAR(255) DEFAULT NULL, INDEX IDX_161498D37ADA1FB5 (color_id), INDEX IDX_161498D3C54C8C93 (type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE collections (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(255) NOT NULL, release_at DATE NOT NULL, name VARCHAR(255) NOT NULL, icon VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE collections_card (collections_id INT NOT NULL, card_id INT NOT NULL, INDEX IDX_7CA795D6242C7AD2 (collections_id), INDEX IDX_7CA795D64ACC9A20 (card_id), PRIMARY KEY(collections_id, card_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE color (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE type (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE card ADD CONSTRAINT FK_161498D37ADA1FB5 FOREIGN KEY (color_id) REFERENCES color (id)');
        $this->addSql('ALTER TABLE card ADD CONSTRAINT FK_161498D3C54C8C93 FOREIGN KEY (type_id) REFERENCES type (id)');
        $this->addSql('ALTER TABLE collections_card ADD CONSTRAINT FK_7CA795D6242C7AD2 FOREIGN KEY (collections_id) REFERENCES collections (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE collections_card ADD CONSTRAINT FK_7CA795D64ACC9A20 FOREIGN KEY (card_id) REFERENCES card (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE collections_card DROP FOREIGN KEY FK_7CA795D64ACC9A20');
        $this->addSql('ALTER TABLE collections_card DROP FOREIGN KEY FK_7CA795D6242C7AD2');
        $this->addSql('ALTER TABLE card DROP FOREIGN KEY FK_161498D37ADA1FB5');
        $this->addSql('ALTER TABLE card DROP FOREIGN KEY FK_161498D3C54C8C93');
        $this->addSql('DROP TABLE card');
        $this->addSql('DROP TABLE collections');
        $this->addSql('DROP TABLE collections_card');
        $this->addSql('DROP TABLE color');
        $this->addSql('DROP TABLE type');
    }
}
