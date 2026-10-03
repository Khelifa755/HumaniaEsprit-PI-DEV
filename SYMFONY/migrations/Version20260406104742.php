<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260406104742 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fix commentaire authorId FK to reference users instead of utilisateur';
    }

    public function up(Schema $schema): void
    {
        // Disable FK checks temporarily
        $this->addSql('SET FOREIGN_KEY_CHECKS=0');
        
        // Clean up orphaned rows
        $this->addSql('UPDATE commentaire c SET c.authorId = NULL WHERE c.authorId NOT IN (SELECT id FROM users)');
        
        // Drop old foreign key pointing to utilisateur
        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY commentaire_ibfk_2');
        
        // Add new foreign key pointing to users table
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT commentaire_ibfk_2 FOREIGN KEY (authorId) REFERENCES users (id) ON DELETE CASCADE');
        
        // Re-enable FK checks
        $this->addSql('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down(Schema $schema): void
    {
        // Revert if needed
        $this->addSql('SET FOREIGN_KEY_CHECKS=0');
        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY commentaire_ibfk_2');
        $this->addSql('SET FOREIGN_KEY_CHECKS=1');
    }
}
