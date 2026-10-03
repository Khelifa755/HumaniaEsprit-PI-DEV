<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260406103856 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Clean orphaned commentaire and reaction rows with missing authors';
    }

    public function up(Schema $schema): void
    {
        // Disable FK checks temporarily
        $this->addSql('SET FOREIGN_KEY_CHECKS=0');
        
        // Set authorId to NULL for commentaire with non-existent authors
        $this->addSql('UPDATE commentaire c SET c.authorId = NULL WHERE c.authorId NOT IN (SELECT id FROM users)');
        
        // Set userId to NULL for reaction with non-existent users
        $this->addSql('UPDATE reaction r SET r.userId = NULL WHERE r.userId NOT IN (SELECT id FROM users)');
        
        // Re-enable FK checks
        $this->addSql('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down(Schema $schema): void
    {
        // No-op: data cleanup is permanent
    }
}
