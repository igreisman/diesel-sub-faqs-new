#!/bin/bash
# Helper script to backup production using credentials from .env.production

# Check if .env.production exists
if [ ! -f .env.production ]; then
    echo "❌ Error: .env.production file not found!"
    echo ""
    echo "Please create .env.production from the example:"
    echo "  cp .env.production.example .env.production"
    echo "  # Then edit .env.production with your production credentials"
    exit 1
fi

# Load production credentials
export $(grep -v '^#' .env.production | xargs)

# Run the production backup script
./backup-prod.sh "$@"
