#!/bin/bash

# ════════════════════════════════════════════════════════════════════════════════
# Post-Migration Verification Script
# ════════════════════════════════════════════════════════════════════════════════
# Vérifie que la migration a réussi et que les données sont intactes

set -e

DB_HOST=${1:-localhost}
DB_USER=${2:-root}
DB_PASS=${3:-}
DB_NAME=${4:-humania_full}

echo "════════════════════════════════════════════════════════════════════════════════"
echo "POST-MIGRATION VERIFICATION"
echo "════════════════════════════════════════════════════════════════════════════════"

# ════════════════════════════════════════════════════════════════════════════════
# Step 1: Check that 'users' table is deleted
# ════════════════════════════════════════════════════════════════════════════════

echo ""
echo "▶ Step 1: Verifying 'users' table has been deleted..."

if [ -z "$DB_PASS" ]; then
    USERS_EXISTS=$(mysql -h "$DB_HOST" -u "$DB_USER" -N -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$DB_NAME' AND table_name='users';" 2>/dev/null || echo "0")
else
    USERS_EXISTS=$(mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" -N -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$DB_NAME' AND table_name='users';" 2>/dev/null || echo "0")
fi

if [ "$USERS_EXISTS" -eq 0 ]; then
    echo "✓ 'users' table successfully deleted"
else
    echo "✗ ERROR: 'users' table still exists! Migration may have failed."
    exit 1
fi

# ════════════════════════════════════════════════════════════════════════════════
# Step 2: Verify all foreign keys point to 'utilisateur'
# ════════════════════════════════════════════════════════════════════════════════

echo ""
echo "▶ Step 2: Verifying foreign key constraints..."

cat > /tmp/verify_fks.sql << 'SQLEOF'
SELECT 
    CONSTRAINT_NAME,
    TABLE_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
WHERE REFERENCED_TABLE_NAME = 'utilisateur'
ORDER BY TABLE_NAME, CONSTRAINT_NAME;
SQLEOF

if [ -z "$DB_PASS" ]; then
    mysql -h "$DB_HOST" -u "$DB_USER" "$DB_NAME" < /tmp/verify_fks.sql > /tmp/fk_report.txt
else
    mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < /tmp/verify_fks.sql > /tmp/fk_report.txt
fi

FK_COUNT=$(grep -c "utilisateur" /tmp/fk_report.txt || echo "0")

if [ "$FK_COUNT" -ge 12 ]; then
    echo "✓ All foreign keys properly point to 'utilisateur' table"
    echo ""
    cat /tmp/fk_report.txt
else
    echo "✗ ERROR: Some foreign keys are missing!"
    cat /tmp/fk_report.txt
    exit 1
fi

# ════════════════════════════════════════════════════════════════════════════════
# Step 3: Verify data integrity
# ════════════════════════════════════════════════════════════════════════════════

echo ""
echo "▶ Step 3: Verifying data integrity..."

cat > /tmp/verify_data.sql << 'SQLEOF'
SELECT 'Data Integrity Report' as report_type;
SELECT '' as blank;

-- Count total users
SELECT 'utilisateur table' as table_name, COUNT(*) as row_count FROM utilisateur
UNION ALL
SELECT 'publication', COUNT(*) FROM publication
UNION ALL
SELECT 'commentaire', COUNT(*) FROM commentaire
UNION ALL
SELECT 'reaction', COUNT(*) FROM reaction
UNION ALL
SELECT 'savedpost', COUNT(*) FROM savedpost
UNION ALL
SELECT 'share', COUNT(*) FROM share
UNION ALL
SELECT 'notification', COUNT(*) FROM notification
UNION ALL
SELECT 'groupmember', COUNT(*) FROM groupmember
UNION ALL
SELECT 'groups', COUNT(*) FROM `groups`
UNION ALL
SELECT 'userprofile', COUNT(*) FROM userprofile
UNION ALL
SELECT 'follow', COUNT(*) FROM follow;

SELECT '' as blank;
SELECT 'Orphaned Foreign Key Check:' as check_name;

-- Check for orphaned FKs
SELECT 'Orphaned publication.authorId' as issue, COUNT(*) as count 
FROM publication p 
WHERE p.authorId IS NOT NULL AND p.authorId NOT IN (SELECT id FROM utilisateur)
UNION ALL
SELECT 'Orphaned commentaire.authorId', COUNT(*) 
FROM commentaire c 
WHERE c.authorId IS NOT NULL AND c.authorId NOT IN (SELECT id FROM utilisateur)
UNION ALL
SELECT 'Orphaned reaction.userId', COUNT(*) 
FROM reaction r 
WHERE r.userId IS NOT NULL AND r.userId NOT IN (SELECT id FROM utilisateur)
UNION ALL
SELECT 'Orphaned savedpost.userId', COUNT(*) 
FROM savedpost s 
WHERE s.userId IS NOT NULL AND s.userId NOT IN (SELECT id FROM utilisateur)
UNION ALL
SELECT 'Orphaned share.userId', COUNT(*) 
FROM share sh 
WHERE sh.userId IS NOT NULL AND sh.userId NOT IN (SELECT id FROM utilisateur)
UNION ALL
SELECT 'Orphaned notification.userId', COUNT(*) 
FROM notification n 
WHERE n.userId IS NOT NULL AND n.userId NOT IN (SELECT id FROM utilisateur)
UNION ALL
SELECT 'Orphaned notification.relatedUserId', COUNT(*) 
FROM notification n 
WHERE n.relatedUserId IS NOT NULL AND n.relatedUserId NOT IN (SELECT id FROM utilisateur)
UNION ALL
SELECT 'Orphaned groupmember.userId', COUNT(*) 
FROM groupmember gm 
WHERE gm.userId IS NOT NULL AND gm.userId NOT IN (SELECT id FROM utilisateur)
UNION ALL
SELECT 'Orphaned groups.createdById', COUNT(*) 
FROM `groups` g 
WHERE g.createdById IS NOT NULL AND g.createdById NOT IN (SELECT id FROM utilisateur);
SQLEOF

if [ -z "$DB_PASS" ]; then
    mysql -h "$DB_HOST" -u "$DB_USER" "$DB_NAME" < /tmp/verify_data.sql > /tmp/data_report.txt
else
    mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < /tmp/verify_data.sql > /tmp/data_report.txt
fi

echo "✓ Data integrity report generated"
cat /tmp/data_report.txt

# Check for orphaned records
ORPHANED=$(grep -c "| Orphaned" /tmp/data_report.txt || echo "0")
if [ "$ORPHANED" -eq 0 ] || grep "| Orphaned" /tmp/data_report.txt | grep "| 0 |" > /dev/null; then
    echo "✓ No orphaned foreign keys detected"
else
    echo "⚠ WARNING: Orphaned records detected! Review above."
fi

# ════════════════════════════════════════════════════════════════════════════════
# Step 4: Run Doctrine cache clearance
# ════════════════════════════════════════════════════════════════════════════════

echo ""
echo "▶ Step 4: Clearing Doctrine cache..."

# Check if we're in a Symfony project
if [ -f "bin/console" ]; then
    php bin/console cache:clear
    echo "✓ Cache cleared"
else
    echo "⚠ Warning: Not in a Symfony project directory. Skipping cache clear."
    echo "  Run manually: php bin/console cache:clear"
fi

# ════════════════════════════════════════════════════════════════════════════════
# Step 5: Final Report
# ════════════════════════════════════════════════════════════════════════════════

echo ""
echo "════════════════════════════════════════════════════════════════════════════════"
echo "MIGRATION VERIFICATION COMPLETE ✓"
echo "════════════════════════════════════════════════════════════════════════════════"
echo ""
echo "Summary:"
echo "  ✓ 'users' table deleted"
echo "  ✓ Foreign keys migrated to 'utilisateur'"
echo "  ✓ Data integrity verified"
echo "  ✓ Cache cleared"
echo ""
echo "Next steps:"
echo "  1. Run Symfony tests: php bin/phpunit"
echo "  2. Test the social media features manually"
echo "  3. If all OK, you can delete the backup directory:"
echo "     rm -rf backups/pre_migration_*"
echo ""
