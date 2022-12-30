<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221229171225 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE card (id INT AUTO_INCREMENT NOT NULL, collection_id INT NOT NULL, name VARCHAR(255) NOT NULL, picture VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, color VARCHAR(255) NOT NULL, description VARCHAR(4000) NOT NULL, artist_name VARCHAR(255) NOT NULL, INDEX IDX_161498D3514956FD (collection_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE collection_card (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(255) NOT NULL, release_date DATE NOT NULL, name VARCHAR(255) NOT NULL, icon VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE card ADD CONSTRAINT FK_161498D3514956FD FOREIGN KEY (collection_id) REFERENCES collection_card (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE card DROP FOREIGN KEY FK_161498D3514956FD');
        $this->addSql('DROP TABLE card');
        $this->addSql('DROP TABLE collection_card');
    }
}
