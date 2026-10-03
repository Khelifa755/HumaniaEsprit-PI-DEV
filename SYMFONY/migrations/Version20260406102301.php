<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260406102301 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Make authorId nullable in publication table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE publication MODIFY authorId INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE publication MODIFY authorId INT NOT NULL');
    }
}
