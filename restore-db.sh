#!/bin/bash
# Database restore script for Diesel-Electric Submarine FAQs

# Check if backup file is provided
if [ -z "$1" ]; then
    echo "Usage: ./restore-db.sh <backup-file.sql>"
    echo ""
    echo "Available backups:"
    ls -lh backups/*.sql 2>/dev/null | tail -10 || echo "No backups found in ./backups/"
    exit 1
fi

BACKUP_FILE="$1"

# Check if file exists
if [ ! -f "$BACKUP_FILE" ]; then
    echo "❌ Error: File '$BACKUP_FILE' not found!"
    exit 1
fi

# Database credentials
DB_USER="dieselsu_dbuser"
DB_PASS="codjuw-xojWo6-datqem"
DB_NAME="dieselsu_faqs"

echo "⚠️  WARNING: This will replace your current database with the backup!"
echo "Restoring from: $BACKUP_FILE"
echo ""
read -p "Are you sure? (yes/no): " -r
echo

if [[ ! $REPLY =~ ^[Yy][Ee][Ss]$ ]]; then
    echo "Restore cancelled."
    exit 0
fi

echo "Restoring database..."

docker-compose exec -T db mariadb \
  -u"$DB_USER" \
  -p"$DB_PASS" \
  "$DB_NAME" < "$BACKUP_FILE"

if [ $? -eq 0 ]; then
    echo "✅ Database restored successfully!"
else
    echo "❌ Restore failed!"
    exit 1
fi
