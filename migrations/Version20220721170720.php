<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220721170720 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute les couleurs.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO color(id, abbr, name) VALUES (1, 'W', 'White')");
        $this->addSql("INSERT INTO color(id, abbr, name) VALUES (2, 'U', 'Blue')");
        $this->addSql("INSERT INTO color(id, abbr, name) VALUES (3, 'B', 'Black')");
        $this->addSql("INSERT INTO color(id, abbr, name) VALUES (4, 'G', 'Green')");
        $this->addSql("INSERT INTO color(id, abbr, name) VALUES (5, 'R', 'Red')");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM color');
    }
}
