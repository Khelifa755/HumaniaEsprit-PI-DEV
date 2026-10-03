-- ════════════════════════════════════════════════════════════════════════════════
-- Migration SQL: Users Table Consolidation
-- From 'users' table to 'utilisateur' table
-- ════════════════════════════════════════════════════════════════════════════════
-- Date: 2026-04-12
-- Purpose: Unify user management by pointing all social media tables to 'utilisateur'
-- ════════════════════════════════════════════════════════════════════════════════

-- ════════════════════════════════════════════════════════════════════════════════
-- PHASE 1: Verify data consistency
-- ════════════════════════════════════════════════════════════════════════════════

-- Check if all users.id exist in utilisateur.id
SELECT 'Checking user alignment...' as step;
SELECT DISTINCT u.id FROM users u WHERE NOT EXISTS (SELECT 1 FROM utilisateur ut WHERE ut.id = u.id);

-- Count affected rows in each table
SELECT 'publication' as table_name, COUNT(*) as affected_rows FROM publication WHERE authorId IS NOT NULL
UNION ALL
SELECT 'commentaire', COUNT(*) FROM commentaire WHERE authorId IS NOT NULL
UNION ALL
SELECT 'reaction', COUNT(*) FROM reaction WHERE userId IS NOT NULL
UNION ALL
SELECT 'savedpost', COUNT(*) FROM savedpost WHERE userId IS NOT NULL
UNION ALL
SELECT 'share', COUNT(*) FROM share WHERE userId IS NOT NULL
UNION ALL
SELECT 'notification', COUNT(*) FROM notification WHERE userId IS NOT NULL OR relatedUserId IS NOT NULL
UNION ALL
SELECT 'groupmember', COUNT(*) FROM groupmember WHERE userId IS NOT NULL
UNION ALL
SELECT 'groups', COUNT(*) FROM `groups` WHERE createdById IS NOT NULL
UNION ALL
SELECT 'userprofile', COUNT(*) FROM userprofile WHERE userId IS NOT NULL
UNION ALL
SELECT 'follow', COUNT(*) FROM follow WHERE followerId IS NOT NULL OR followingId IS NOT NULL;

-- ════════════════════════════════════════════════════════════════════════════════
-- PHASE 2: Drop existing foreign key constraints from 'users' table
-- ════════════════════════════════════════════════════════════════════════════════

ALTER TABLE publication DROP FOREIGN KEY IDX_AF3C6779A196F9FD;
ALTER TABLE commentaire DROP FOREIGN KEY IDX_67F068BCA196F9FD;
ALTER TABLE reaction DROP FOREIGN KEY IDX_A4D707F764B64DCC;
ALTER TABLE savedpost DROP FOREIGN KEY IDX_F39B527F64B64DCC;
ALTER TABLE share DROP FOREIGN KEY FK_EF069D5A64B64DCC;
ALTER TABLE notification DROP FOREIGN KEY IDX_BF5476CA64B64DCC;
ALTER TABLE notification DROP FOREIGN KEY IDX_BF5476CA99A3424D;
ALTER TABLE groupmember DROP FOREIGN KEY IDX_AAF03D8364B64DCC;
ALTER TABLE `groups` DROP FOREIGN KEY IDX_F06D3970774D5986;
ALTER TABLE userprofile DROP FOREIGN KEY IDX_1D3656B164B64DCC;
ALTER TABLE follow DROP FOREIGN KEY IDX_68344470F542AA03;
ALTER TABLE follow DROP FOREIGN KEY IDX_68344470CCE76DFF;

-- ════════════════════════════════════════════════════════════════════════════════
-- PHASE 3: Add new foreign key constraints pointing to 'utilisateur' table
-- ════════════════════════════════════════════════════════════════════════════════

ALTER TABLE publication ADD CONSTRAINT FK_AF3C6779A196F9FD FOREIGN KEY (authorId) REFERENCES utilisateur (id) ON DELETE CASCADE;
ALTER TABLE commentaire ADD CONSTRAINT IDX_67F068BCA196F9FD FOREIGN KEY (authorId) REFERENCES utilisateur (id) ON DELETE CASCADE;
ALTER TABLE reaction ADD CONSTRAINT IDX_A4D707F764B64DCC FOREIGN KEY (userId) REFERENCES utilisateur (id) ON DELETE CASCADE;
ALTER TABLE savedpost ADD CONSTRAINT IDX_F39B527F64B64DCC FOREIGN KEY (userId) REFERENCES utilisateur (id) ON DELETE CASCADE;
ALTER TABLE share ADD CONSTRAINT FK_EF069D5A64B64DCC FOREIGN KEY (userId) REFERENCES utilisateur (id) ON DELETE CASCADE;
ALTER TABLE notification ADD CONSTRAINT IDX_BF5476CA64B64DCC FOREIGN KEY (userId) REFERENCES utilisateur (id) ON DELETE CASCADE;
ALTER TABLE notification ADD CONSTRAINT IDX_BF5476CA99A3424D FOREIGN KEY (relatedUserId) REFERENCES utilisateur (id) ON DELETE CASCADE;
ALTER TABLE groupmember ADD CONSTRAINT IDX_AAF03D8364B64DCC FOREIGN KEY (userId) REFERENCES utilisateur (id) ON DELETE CASCADE;
ALTER TABLE `groups` ADD CONSTRAINT IDX_F06D3970774D5986 FOREIGN KEY (createdById) REFERENCES utilisateur (id) ON DELETE CASCADE;
ALTER TABLE userprofile ADD CONSTRAINT IDX_1D3656B164B64DCC FOREIGN KEY (userId) REFERENCES utilisateur (id) ON DELETE CASCADE;
ALTER TABLE follow ADD CONSTRAINT IDX_68344470F542AA03 FOREIGN KEY (followerId) REFERENCES utilisateur (id) ON DELETE CASCADE;
ALTER TABLE follow ADD CONSTRAINT IDX_68344470CCE76DFF FOREIGN KEY (followingId) REFERENCES utilisateur (id) ON DELETE CASCADE;

-- ════════════════════════════════════════════════════════════════════════════════
-- PHASE 4: Drop the redundant 'users' table
-- ════════════════════════════════════════════════════════════════════════════════

DROP TABLE users;

-- ════════════════════════════════════════════════════════════════════════════════
-- PHASE 5: Verify migration success
-- ════════════════════════════════════════════════════════════════════════════════

SELECT 'Migration completed successfully!' as status;
SELECT COUNT(*) as total_users FROM utilisateur;
SELECT COUNT(DISTINCT authorId) as authors_with_publications FROM publication;
SELECT COUNT(DISTINCT authorId) as authors_with_comments FROM commentaire;

-- Show all foreign keys referencing utilisateur from social tables
SELECT 
    CONSTRAINT_NAME, 
    TABLE_NAME, 
    COLUMN_NAME, 
    REFERENCED_TABLE_NAME, 
    REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
WHERE REFERENCED_TABLE_NAME = 'utilisateur' 
ORDER BY TABLE_NAME;
