#!/bin/bash
# Production Database Backup Script for Diesel-Electric Submarine FAQs
# This script backs up the production database (e.g., HostGator or remote server)

# Set backup directory
BACKUP_DIR="./backups/production"
mkdir -p "$BACKUP_DIR"

# Generate timestamp
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

# Production database credentials
# Option 1: Set these environment variables before running:
#   export PROD_DB_HOST="your-host.com"
#   export PROD_DB_USER="dieselsu_dbuser"
#   export PROD_DB_PASS="your-password"
#   export PROD_DB_NAME="dieselsu_faqs"
#
# Option 2: Or uncomment and set them here (less secure):
# PROD_DB_HOST="your-host.com"
# PROD_DB_USER="dieselsu_dbuser"
# PROD_DB_PASS="your-password"
# PROD_DB_NAME="dieselsu_faqs"

# Check if credentials are set
if [ -z "$PROD_DB_HOST" ] || [ -z "$PROD_DB_USER" ] || [ -z "$PROD_DB_PASS" ] || [ -z "$PROD_DB_NAME" ]; then
    echo "❌ Error: Production database credentials not set!"
    echo ""
    echo "Please set the following environment variables:"
    echo "  export PROD_DB_HOST=\"your-host.com\""
    echo "  export PROD_DB_USER=\"your-username\""
    echo "  export PROD_DB_PASS=\"your-password\""
    echo "  export PROD_DB_NAME=\"your-database\""
    echo ""
    echo "Or edit this script and uncomment the credential variables."
    exit 1
fi

echo "Starting production database backup from $PROD_DB_HOST..."

# Check if SSH tunnel is configured
if [ -n "$PROD_SSH_HOST" ] && [ -n "$PROD_SSH_USER" ]; then
    echo "Using SSH tunnel via $PROD_SSH_HOST..."
    SSH_PORT="${PROD_SSH_PORT:-22}"
    
    # Full database backup using SSH tunnel
    ssh -p "$SSH_PORT" "$PROD_SSH_USER@$PROD_SSH_HOST" \
        "mysqldump -h localhost -u'$PROD_DB_USER' -p'$PROD_DB_PASS' --single-transaction --quick --lock-tables=false '$PROD_DB_NAME'" \
        > "$BACKUP_DIR/prod_full_backup_$TIMESTAMP.sql"
else
    # Direct connection (if allowed)
    echo "Using direct database connection..."
    mysqldump \
      --host="$PROD_DB_HOST" \
      --user="$PROD_DB_USER" \
      --password="$PROD_DB_PASS" \
      --single-transaction \
      --quick \
      --lock-tables=false \
      "$PROD_DB_NAME" > "$BACKUP_DIR/prod_full_backup_$TIMESTAMP.sql"
fi

if [ $? -eq 0 ]; then
    SIZE=$(du -h "$BACKUP_DIR/prod_full_backup_$TIMESTAMP.sql" | cut -f1)
    echo "✅ Production full backup saved: $BACKUP_DIR/prod_full_backup_$TIMESTAMP.sql ($SIZE)"
else
    echo "❌ Backup failed!"
    exit 1
fi

# Also backup just the lost_submarines table
if [ -n "$PROD_SSH_HOST" ] && [ -n "$PROD_SSH_USER" ]; then
    ssh -p "$SSH_PORT" "$PROD_SSH_USER@$PROD_SSH_HOST" \
        "mysqldump -h localhost -u'$PROD_DB_USER' -p'$PROD_DB_PASS' --single-transaction --quick --lock-tables=false '$PROD_DB_NAME' lost_submarines" \
        > "$BACKUP_DIR/prod_lost_submarines_$TIMESTAMP.sql"
else
    mysqldump \
      --host="$PROD_DB_HOST" \
      --user="$PROD_DB_USER" \
      --password="$PROD_DB_PASS" \
      --single-transaction \
      --quick \
      --lock-tables=false \
      "$PROD_DB_NAME" lost_submarines > "$BACKUP_DIR/prod_lost_submarines_$TIMESTAMP.sql"
fi

if [ $? -eq 0 ]; then
    SIZE=$(du -h "$BACKUP_DIR/prod_lost_submarines_$TIMESTAMP.sql" | cut -f1)
    echo "✅ Production lost submarines backup saved: $BACKUP_DIR/prod_lost_submarines_$TIMESTAMP.sql ($SIZE)"
fi

# Keep only last 20 production backups (production is more important)
echo "Cleaning old production backups (keeping last 20)..."
cd "$BACKUP_DIR"
ls -t prod_full_backup_*.sql 2>/dev/null | tail -n +21 | xargs -I {} rm -- {}
ls -t prod_lost_submarines_*.sql 2>/dev/null | tail -n +21 | xargs -I {} rm -- {}

echo "✅ Production backup complete!"
