#!/bin/bash
# Backup script for SFTP-only access
# This triggers a remote backup and downloads it via SFTP

# Load production credentials
if [ ! -f .env.production ]; then
    echo "❌ Error: .env.production file not found!"
    exit 1
fi

export $(grep -v '^#' .env.production | xargs)

# Configuration
BACKUP_DIR="./backups/production"
mkdir -p "$BACKUP_DIR"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

# Secret key (must match the one in remote-backup.php)
SECRET_KEY="hilds-78kdI3-ur73kldf-92jdls"

# Ensure SSH key is loaded
if ! ssh-add -l | grep -q "id_ed25519"; then
    echo "🔑 SSH key not loaded. Adding to ssh-agent..."
    ssh-add ~/.ssh/id_ed25519
    if [ $? -ne 0 ]; then
        echo "❌ Failed to add SSH key to agent"
        exit 1
    fi
fi

echo "🚀 Triggering remote backup generation..."

# Trigger the backup on the remote server
RESPONSE=$(curl -s "https://www.dieselsubs.com/remote-backup.php?key=$SECRET_KEY")

if [[ $RESPONSE == *"✅"* ]]; then
    echo "$RESPONSE"
    
    # Extract filename from response
    REMOTE_FILE=$(echo "$RESPONSE" | grep "Download:" | awk '{print $2}')
    
    if [ -n "$REMOTE_FILE" ]; then
        echo ""
        echo "📥 Downloading backup via SFTP..."
        
        # Download using SFTP
        sftp -P "${PROD_SSH_PORT:-22}" "$PROD_SSH_USER@$PROD_SSH_HOST" <<EOF
cd public_html/backups
get $REMOTE_FILE $BACKUP_DIR/$REMOTE_FILE
bye
EOF
        
        if [ $? -eq 0 ]; then
            echo "✅ Backup downloaded to: $BACKUP_DIR/$REMOTE_FILE"
            
            # Clean old backups (keep last 20)
            cd "$BACKUP_DIR"
            ls -t prod_backup_*.sql 2>/dev/null | tail -n +21 | xargs -I {} rm -- {}
            
            echo "✅ Production backup complete!"
        else
            echo "❌ SFTP download failed!"
            exit 1
        fi
    fi
else
    echo "❌ Remote backup failed!"
    echo "$RESPONSE"
    exit 1
fi
