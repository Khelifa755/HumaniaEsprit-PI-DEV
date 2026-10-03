<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migrate User entity from 'users' table to 'utilisateur' table
 * Redirect all social media foreign keys from users.id to utilisateur.id
 * Drop the redundant 'users' table
 */
final class Version20260412000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Migrate Users entity to utilisateur table and update all social FKs';
    }

    public function up(Schema $schema): void
    {
        // ════════════════════════════════════════════════════════════════════════════════
        // Step 1: Drop foreign key constraints referencing users table
        // ════════════════════════════════════════════════════════════════════════════════

        // Publication.authorId -> users.id
        $this->addSql('ALTER TABLE publication DROP FOREIGN KEY IDX_AF3C6779A196F9FD');

        // Commentaire.authorId -> users.id
        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY IDX_67F068BCA196F9FD');

        // Reaction.userId -> users.id
        $this->addSql('ALTER TABLE reaction DROP FOREIGN KEY IDX_A4D707F764B64DCC');

        // Savedpost.userId -> users.id
        $this->addSql('ALTER TABLE savedpost DROP FOREIGN KEY IDX_F39B527F64B64DCC');

        // Share.userId -> users.id
        $this->addSql('ALTER TABLE share DROP FOREIGN KEY FK_EF069D5A64B64DCC');

        // Notification.userId -> users.id
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY IDX_BF5476CA64B64DCC');

        // Notification.relatedUserId -> users.id
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY IDX_BF5476CA99A3424D');

        // Groupmember.userId -> users.id
        $this->addSql('ALTER TABLE groupmember DROP FOREIGN KEY IDX_AAF03D8364B64DCC');

        // Groups.createdById -> users.id
        $this->addSql('ALTER TABLE `groups` DROP FOREIGN KEY IDX_F06D3970774D5986');

        // Userprofile.userId -> users.id
        $this->addSql('ALTER TABLE userprofile DROP FOREIGN KEY IDX_1D3656B164B64DCC');

        // Follow.followerId -> users.id
        $this->addSql('ALTER TABLE follow DROP FOREIGN KEY IDX_68344470F542AA03');

        // Follow.followingId -> users.id
        $this->addSql('ALTER TABLE follow DROP FOREIGN KEY IDX_68344470CCE76DFF');

        // ════════════════════════════════════════════════════════════════════════════════
        // Step 2: Add the new foreign key constraints to utilisateur table
        // ════════════════════════════════════════════════════════════════════════════════

        // Publication.authorId -> utilisateur.id
        $this->addSql('ALTER TABLE publication ADD CONSTRAINT FK_AF3C6779A196F9FD FOREIGN KEY (authorId) REFERENCES utilisateur (id) ON DELETE CASCADE');

        // Commentaire.authorId -> utilisateur.id
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT IDX_67F068BCA196F9FD FOREIGN KEY (authorId) REFERENCES utilisateur (id) ON DELETE CASCADE');

        // Reaction.userId -> utilisateur.id
        $this->addSql('ALTER TABLE reaction ADD CONSTRAINT IDX_A4D707F764B64DCC FOREIGN KEY (userId) REFERENCES utilisateur (id) ON DELETE CASCADE');

        // Savedpost.userId -> utilisateur.id
        $this->addSql('ALTER TABLE savedpost ADD CONSTRAINT IDX_F39B527F64B64DCC FOREIGN KEY (userId) REFERENCES utilisateur (id) ON DELETE CASCADE');

        // Share.userId -> utilisateur.id
        $this->addSql('ALTER TABLE share ADD CONSTRAINT FK_EF069D5A64B64DCC FOREIGN KEY (userId) REFERENCES utilisateur (id) ON DELETE CASCADE');

        // Notification.userId -> utilisateur.id
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT IDX_BF5476CA64B64DCC FOREIGN KEY (userId) REFERENCES utilisateur (id) ON DELETE CASCADE');

        // Notification.relatedUserId -> utilisateur.id
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT IDX_BF5476CA99A3424D FOREIGN KEY (relatedUserId) REFERENCES utilisateur (id) ON DELETE CASCADE');

        // Groupmember.userId -> utilisateur.id
        $this->addSql('ALTER TABLE groupmember ADD CONSTRAINT IDX_AAF03D8364B64DCC FOREIGN KEY (userId) REFERENCES utilisateur (id) ON DELETE CASCADE');

        // Groups.createdById -> utilisateur.id
        $this->addSql('ALTER TABLE `groups` ADD CONSTRAINT IDX_F06D3970774D5986 FOREIGN KEY (createdById) REFERENCES utilisateur (id) ON DELETE CASCADE');

        // Userprofile.userId -> utilisateur.id
        $this->addSql('ALTER TABLE userprofile ADD CONSTRAINT IDX_1D3656B164B64DCC FOREIGN KEY (userId) REFERENCES utilisateur (id) ON DELETE CASCADE');

        // Follow.followerId -> utilisateur.id
        $this->addSql('ALTER TABLE follow ADD CONSTRAINT IDX_68344470F542AA03 FOREIGN KEY (followerId) REFERENCES utilisateur (id) ON DELETE CASCADE');

        // Follow.followingId -> utilisateur.id
        $this->addSql('ALTER TABLE follow ADD CONSTRAINT IDX_68344470CCE76DFF FOREIGN KEY (followingId) REFERENCES utilisateur (id) ON DELETE CASCADE');

        // ════════════════════════════════════════════════════════════════════════════════
        // Step 3: Drop the redundant 'users' table
        // ════════════════════════════════════════════════════════════════════════════════
        $this->addSql('DROP TABLE users');
    }

    public function down(Schema $schema): void
    {
        // ROLLBACK: Restore the users table and revert all FKs
        // This is complex because we need to recreate the entire users table structure
        // For rollback, you would need to manually restore from backup or implement the reverse

        // Recreate the users table with same structure as before
        $this->addSql(
            'CREATE TABLE users (
                id INT AUTO_INCREMENT NOT NULL,
                username VARCHAR(50) NOT NULL,
                email VARCHAR(100) NOT NULL,
                password VARCHAR(255) NOT NULL,
                firstName VARCHAR(50) NOT NULL,
                lastName VARCHAR(50) NOT NULL,
                bio LONGTEXT DEFAULT NULL,
                avatarUrl VARCHAR(500) DEFAULT NULL,
                createdAt DATETIME NOT NULL,
                updatedAt DATETIME NOT NULL,
                isActive TINYINT NOT NULL,
                role VARCHAR(20) NOT NULL,
                lastSeen DATETIME DEFAULT NULL,
                isOnline TINYINT NOT NULL,
                UNIQUE KEY email (email),
                UNIQUE KEY username (username),
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`'
        );

        // Restore all foreign key constraints to users table
        $this->addSql('ALTER TABLE publication DROP FOREIGN KEY FK_AF3C6779A196F9FD');
        $this->addSql('ALTER TABLE publication ADD CONSTRAINT IDX_AF3C6779A196F9FD FOREIGN KEY (authorId) REFERENCES users (id) ON DELETE CASCADE');

        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY IDX_67F068BCA196F9FD');
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT IDX_67F068BCA196F9FD FOREIGN KEY (authorId) REFERENCES users (id) ON DELETE CASCADE');

        $this->addSql('ALTER TABLE reaction DROP FOREIGN KEY IDX_A4D707F764B64DCC');
        $this->addSql('ALTER TABLE reaction ADD CONSTRAINT IDX_A4D707F764B64DCC FOREIGN KEY (userId) REFERENCES users (id) ON DELETE CASCADE');

        $this->addSql('ALTER TABLE savedpost DROP FOREIGN KEY IDX_F39B527F64B64DCC');
        $this->addSql('ALTER TABLE savedpost ADD CONSTRAINT IDX_F39B527F64B64DCC FOREIGN KEY (userId) REFERENCES users (id) ON DELETE CASCADE');

        $this->addSql('ALTER TABLE share DROP FOREIGN KEY FK_EF069D5A64B64DCC');
        $this->addSql('ALTER TABLE share ADD CONSTRAINT FK_EF069D5A64B64DCC FOREIGN KEY (userId) REFERENCES users (id) ON DELETE CASCADE');

        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY IDX_BF5476CA64B64DCC');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT IDX_BF5476CA64B64DCC FOREIGN KEY (userId) REFERENCES users (id) ON DELETE CASCADE');

        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY IDX_BF5476CA99A3424D');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT IDX_BF5476CA99A3424D FOREIGN KEY (relatedUserId) REFERENCES users (id) ON DELETE CASCADE');

        $this->addSql('ALTER TABLE groupmember DROP FOREIGN KEY IDX_AAF03D8364B64DCC');
        $this->addSql('ALTER TABLE groupmember ADD CONSTRAINT IDX_AAF03D8364B64DCC FOREIGN KEY (userId) REFERENCES users (id) ON DELETE CASCADE');

        $this->addSql('ALTER TABLE `groups` DROP FOREIGN KEY IDX_F06D3970774D5986');
        $this->addSql('ALTER TABLE `groups` ADD CONSTRAINT IDX_F06D3970774D5986 FOREIGN KEY (createdById) REFERENCES users (id) ON DELETE CASCADE');

        $this->addSql('ALTER TABLE userprofile DROP FOREIGN KEY IDX_1D3656B164B64DCC');
        $this->addSql('ALTER TABLE userprofile ADD CONSTRAINT IDX_1D3656B164B64DCC FOREIGN KEY (userId) REFERENCES users (id) ON DELETE CASCADE');

        $this->addSql('ALTER TABLE follow DROP FOREIGN KEY IDX_68344470F542AA03');
        $this->addSql('ALTER TABLE follow ADD CONSTRAINT IDX_68344470F542AA03 FOREIGN KEY (followerId) REFERENCES users (id) ON DELETE CASCADE');

        $this->addSql('ALTER TABLE follow DROP FOREIGN KEY IDX_68344470CCE76DFF');
        $this->addSql('ALTER TABLE follow ADD CONSTRAINT IDX_68344470CCE76DFF FOREIGN KEY (followingId) REFERENCES users (id) ON DELETE CASCADE');
    }
}
