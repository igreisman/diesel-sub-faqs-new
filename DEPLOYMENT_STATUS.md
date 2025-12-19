# Deployment Status

## Current Status: Local Development

- Site configured for local development with PHP
- Uses local MySQL database or PlanetScale for production
- PHP server can be started with: `php -S localhost:8000`

## Database Connection

Uses environment variables for database configuration:

- DB_HOST
- DB_PORT  
- DB_USER
- DB_PASSWORD
- DB_NAME

Or connection string:
- DATABASE_URL (for cloud databases like PlanetScale)
