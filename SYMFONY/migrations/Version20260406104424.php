<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260406104424 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fix foreign keys for reaction and savedpost tables to reference users instead of utilisateur';
    }

    public function up(Schema $schema): void
    {
        // Disable FK checks temporarily
        $this->addSql('SET FOREIGN_KEY_CHECKS=0');
        
        // Clean up orphaned rows
        $this->addSql('UPDATE reaction r SET r.userId = NULL WHERE r.userId NOT IN (SELECT id FROM users)');
        $this->addSql('UPDATE savedpost s SET s.userId = NULL WHERE s.userId NOT IN (SELECT id FROM users)');
        
        // Drop old foreign keys pointing to utilisateur
        $this->addSql('ALTER TABLE reaction DROP FOREIGN KEY reaction_ibfk_1');
        $this->addSql('ALTER TABLE savedpost DROP FOREIGN KEY savedpost_ibfk_1');
        
        // Add new foreign keys pointing to users table
        $this->addSql('ALTER TABLE reaction ADD CONSTRAINT reaction_ibfk_1 FOREIGN KEY (userId) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE savedpost ADD CONSTRAINT savedpost_ibfk_1 FOREIGN KEY (userId) REFERENCES users (id) ON DELETE CASCADE');
        
        // Re-enable FK checks
        $this->addSql('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down(Schema $schema): void
    {
        // Revert if needed
        $this->addSql('SET FOREIGN_KEY_CHECKS=0');
        $this->addSql('ALTER TABLE reaction DROP FOREIGN KEY reaction_ibfk_1');
        $this->addSql('ALTER TABLE savedpost DROP FOREIGN KEY savedpost_ibfk_1');
        $this->addSql('SET FOREIGN_KEY_CHECKS=1');
    }
}
