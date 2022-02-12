<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220212093454 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create fix Colors entity';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO color (abbreviation, name, basic_land) VALUES ("W", "White", "Plains")');
        $this->addSql('INSERT INTO color (abbreviation, name, basic_land) VALUES ("U", "Blue", "Island")');
        $this->addSql('INSERT INTO color (abbreviation, name, basic_land) VALUES ("B", "Black", "Swamp")');
        $this->addSql('INSERT INTO color (abbreviation, name, basic_land) VALUES ("R", "Red", "Mountain")');
        $this->addSql('INSERT INTO color (abbreviation, name, basic_land) VALUES ("G", "Green", "Forest")');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM color WHERE abbreviation IN ("W", "U", "B", "R", "G")');
    }
}
