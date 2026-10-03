<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Create Mention entity and table
 * Track @mentions in publications and comments
 */
final class Version20260414000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create mention table to track user mentions in posts and comments';
    }

    public function up(Schema $schema): void
    {
        // Create mention table
        $this->addSql('CREATE TABLE mention (
            id INT AUTO_INCREMENT NOT NULL,
            mentionedUserId INT NOT NULL,
            authorId INT NOT NULL,
            publicationId INT NULL,
            commentaireId INT NULL,
            createdAt DATETIME NOT NULL,
            status VARCHAR(50) DEFAULT \'UNREAD\',
            PRIMARY KEY (id),
            FOREIGN KEY (mentionedUserId) REFERENCES utilisateur (id) ON DELETE CASCADE,
            FOREIGN KEY (authorId) REFERENCES utilisateur (id) ON DELETE CASCADE,
            FOREIGN KEY (publicationId) REFERENCES publication (id) ON DELETE CASCADE,
            FOREIGN KEY (commentaireId) REFERENCES commentaire (id) ON DELETE CASCADE,
            INDEX IDX_MENTIONED (mentionedUserId),
            INDEX IDX_AUTHOR (authorId),
            INDEX IDX_PUB (publicationId),
            INDEX IDX_COMMENT (commentaireId)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE mention');
    }
}
