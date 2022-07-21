<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220721175035 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE card ALTER image DROP NOT NULL');
        $this->addSql('ALTER TABLE card ALTER type DROP NOT NULL');
        $this->addSql('ALTER TABLE card ALTER description DROP NOT NULL');
        $this->addSql('ALTER TABLE card ALTER artist DROP NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE card ALTER image SET NOT NULL');
        $this->addSql('ALTER TABLE card ALTER type SET NOT NULL');
        $this->addSql('ALTER TABLE card ALTER description SET NOT NULL');
        $this->addSql('ALTER TABLE card ALTER artist SET NOT NULL');
    }
}
