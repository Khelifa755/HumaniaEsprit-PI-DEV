#!/bin/bash

# ════════════════════════════════════════════════════════════════════════════════
# Pre-Migration Backup & Verification Script
# ════════════════════════════════════════════════════════════════════════════════
# Exécute des vérifications avant la migration Users consolidation

set -e

DB_HOST=${1:-localhost}
DB_USER=${2:-root}
DB_PASS=${3:-}
DB_NAME=${4:-humania_full}
BACKUP_DIR="backups/pre_migration_$(date +%Y%m%d_%H%M%S)"

echo "════════════════════════════════════════════════════════════════════════════════"
echo "PRE-MIGRATION VERIFICATION & BACKUP"
echo "════════════════════════════════════════════════════════════════════════════════"

# Create backup directory
mkdir -p "$BACKUP_DIR"
echo "✓ Backup directory: $BACKUP_DIR"

# ════════════════════════════════════════════════════════════════════════════════
# Step 1: Backup entire database
# ════════════════════════════════════════════════════════════════════════════════

echo ""
echo "▶ Step 1: Creating database backup..."

if [ -z "$DB_PASS" ]; then
    mysqldump -h "$DB_HOST" -u "$DB_USER" "$DB_NAME" > "$BACKUP_DIR/full_dump.sql"
else
    mysqldump -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" > "$BACKUP_DIR/full_dump.sql"
fi

echo "✓ Full database backup: $BACKUP_DIR/full_dump.sql"

# Backup specific tables that will be affected
echo ""
echo "▶ Step 2: Backing up affected tables..."

AFFECTED_TABLES=(
    "users" "utilisateur" "publication" "commentaire" "reaction" 
    "savedpost" "share" "notification" "groupmember" "groups" 
    "userprofile" "follow"
)

for table in "${AFFECTED_TABLES[@]}"; do
    if [ -z "$DB_PASS" ]; then
        mysqldump -h "$DB_HOST" -u "$DB_USER" "$DB_NAME" "$table" > "$BACKUP_DIR/${table}_backup.sql"
    else
        mysqldump -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" "$table" > "$BACKUP_DIR/${table}_backup.sql"
    fi
done

echo "✓ Individual table backups created"

# ════════════════════════════════════════════════════════════════════════════════
# Step 3: Verify data consistency
# ════════════════════════════════════════════════════════════════════════════════

echo ""
echo "▶ Step 3: Verifying data consistency..."

# Create verification SQL script
cat > "$BACKUP_DIR/verify_consistency.sql" << 'SQLEOF'
-- Check if all users.id exist in utilisateur.id
SELECT 'Users not in utilisateur:' as check_name;
SELECT u.id FROM users u WHERE NOT EXISTS (SELECT 1 FROM utilisateur ut WHERE ut.id = u.id);

-- Count rows in each affected table
SELECT '=== AFFECTED TABLES ROW COUNTS ===' as info;
SELECT 'publication' as table_name, COUNT(*) as rows FROM publication
UNION ALL SELECT 'commentaire', COUNT(*) FROM commentaire
UNION ALL SELECT 'reaction', COUNT(*) FROM reaction
UNION ALL SELECT 'savedpost', COUNT(*) FROM savedpost
UNION ALL SELECT 'share', COUNT(*) FROM share
UNION ALL SELECT 'notification', COUNT(*) FROM notification
UNION ALL SELECT 'groupmember', COUNT(*) FROM groupmember
UNION ALL SELECT 'groups', COUNT(*) FROM `groups`
UNION ALL SELECT 'userprofile', COUNT(*) FROM userprofile
UNION ALL SELECT 'follow', COUNT(*) FROM follow;

-- Check for orphaned FKs that might break the migration
SELECT 'Checking for orphaned publication.authorId:' as check_name;
SELECT DISTINCT p.authorId FROM publication p WHERE p.authorId IS NOT NULL AND p.authorId NOT IN (SELECT id FROM users);

SELECT 'Checking for orphaned commentaire.authorId:' as check_name;
SELECT DISTINCT c.authorId FROM commentaire c WHERE c.authorId IS NOT NULL AND c.authorId NOT IN (SELECT id FROM users);

SELECT 'Checking for orphaned reaction.userId:' as check_name;
SELECT DISTINCT r.userId FROM reaction r WHERE r.userId IS NOT NULL AND r.userId NOT IN (SELECT id FROM users);

SELECT 'All consistency checks completed!' as result;
SQLEOF

if [ -z "$DB_PASS" ]; then
    mysql -h "$DB_HOST" -u "$DB_USER" "$DB_NAME" < "$BACKUP_DIR/verify_consistency.sql" > "$BACKUP_DIR/consistency_report.txt" 2>&1
else
    mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$BACKUP_DIR/verify_consistency.sql" > "$BACKUP_DIR/consistency_report.txt" 2>&1
fi

echo "✓ Consistency verification: $BACKUP_DIR/consistency_report.txt"
cat "$BACKUP_DIR/consistency_report.txt"

# ════════════════════════════════════════════════════════════════════════════════
# Step 4: Generate row count snapshot
# ════════════════════════════════════════════════════════════════════════════════

echo ""
echo "▶ Step 4: Generating pre-migration snapshot..."

cat > "$BACKUP_DIR/pre_migration_snapshot.sql" << 'SQLEOF'
-- Pre-migration row count snapshot
SELECT 'publication' as table_name, COUNT(*) as pre_migration_count INTO OUTFILE '$HOME/pre_publication.txt' FROM publication;
SELECT 'commentaire' as table_name, COUNT(*) as pre_migration_count INTO OUTFILE '$HOME/pre_commentaire.txt' FROM commentaire;
SELECT 'reaction' as table_name, COUNT(*) as pre_migration_count INTO OUTFILE '$HOME/pre_reaction.txt' FROM reaction;
SQLEOF

echo "✓ Snapshot prepared"

# ════════════════════════════════════════════════════════════════════════════════
# Step 5: Summary
# ════════════════════════════════════════════════════════════════════════════════

echo ""
echo "════════════════════════════════════════════════════════════════════════════════"
echo "BACKUP & VERIFICATION COMPLETE"
echo "════════════════════════════════════════════════════════════════════════════════"
echo ""
echo "Backup location: $BACKUP_DIR"
echo ""
echo "Files created:"
ls -lh "$BACKUP_DIR"
echo ""
echo "Next steps:"
echo "  1. Review consistency report: cat $BACKUP_DIR/consistency_report.txt"
echo "  2. If all checks pass, run the migration:"
echo "     php bin/console doctrine:migrations:migrate"
echo "  3. After migration, run post-migration verification:"
echo "     bash scripts/post_migration_verify.sh"
echo ""
echo "If migration fails, restore with:"
echo "  mysql -h $DB_HOST -u $DB_USER -p $DB_NAME < $BACKUP_DIR/full_dump.sql"
echo ""
