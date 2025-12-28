#!/bin/bash
# Database backup script for Diesel-Electric Submarine FAQs

# Set backup directory
BACKUP_DIR="./backups"
mkdir -p "$BACKUP_DIR"

# Generate timestamp
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

# Database credentials
DB_USER="dieselsu_dbuser"
DB_PASS="codjuw-xojWo6-datqem"
DB_NAME="dieselsu_faqs"

echo "Starting database backup..."

# Full database backup
docker-compose exec -T db mariadb-dump \
  -u"$DB_USER" \
  -p"$DB_PASS" \
  "$DB_NAME" > "$BACKUP_DIR/full_backup_$TIMESTAMP.sql"

if [ $? -eq 0 ]; then
    echo "✅ Full backup saved: $BACKUP_DIR/full_backup_$TIMESTAMP.sql"
else
    echo "❌ Backup failed!"
    exit 1
fi

# Also backup just the lost_submarines table
docker-compose exec -T db mariadb-dump \
  -u"$DB_USER" \
  -p"$DB_PASS" \
  "$DB_NAME" lost_submarines > "$BACKUP_DIR/lost_submarines_$TIMESTAMP.sql"

if [ $? -eq 0 ]; then
    echo "✅ Lost submarines backup saved: $BACKUP_DIR/lost_submarines_$TIMESTAMP.sql"
fi

# Keep only last 10 backups
echo "Cleaning old backups (keeping last 10)..."
cd "$BACKUP_DIR"
ls -t full_backup_*.sql | tail -n +11 | xargs -I {} rm -- {}
ls -t lost_submarines_*.sql | tail -n +11 | xargs -I {} rm -- {}

echo "✅ Backup complete!"
