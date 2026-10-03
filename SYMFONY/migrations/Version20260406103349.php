<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260406103349 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Clean orphaned publications with missing authors';
    }

    public function up(Schema $schema): void
    {
        // Set authorId to NULL for publications with non-existent authors
        $this->addSql('UPDATE publication p SET p.authorId = NULL WHERE p.authorId NOT IN (SELECT id FROM users)');
    }

    public function down(Schema $schema): void
    {
        // No-op: data cleanup is permanent
    }
}
