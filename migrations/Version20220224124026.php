<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220224124026 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE card_color (card_id INT NOT NULL, color_id INT NOT NULL, INDEX IDX_5E62B92F4ACC9A20 (card_id), INDEX IDX_5E62B92F7ADA1FB5 (color_id), PRIMARY KEY(card_id, color_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE card_color ADD CONSTRAINT FK_5E62B92F4ACC9A20 FOREIGN KEY (card_id) REFERENCES card (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE card_color ADD CONSTRAINT FK_5E62B92F7ADA1FB5 FOREIGN KEY (color_id) REFERENCES color (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE card DROP FOREIGN KEY FK_161498D37ADA1FB5');
        $this->addSql('DROP INDEX IDX_161498D37ADA1FB5 ON card');
        $this->addSql('ALTER TABLE card DROP color_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE card_color');
        $this->addSql('ALTER TABLE card ADD color_id INT NOT NULL');
        $this->addSql('ALTER TABLE card ADD CONSTRAINT FK_161498D37ADA1FB5 FOREIGN KEY (color_id) REFERENCES color (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_161498D37ADA1FB5 ON card (color_id)');
    }
}
