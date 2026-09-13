#!/bin/bash
# Railway pre-deploy for the BayCIS app service.
# Runs AFTER the build (private network to Postgres is available here),
# so migrations go here — never in the build command.
# Dashboard → App service → Settings → Deploy → Pre-Deploy Command:
#   chmod +x ./railway/init-app.sh && sh ./railway/init-app.sh
# Make executable locally: chmod +x railway/init-app.sh

# Exit if any command fails
set -e

# Run migrations on the Railway Postgres database
php artisan migrate --force

# Clear stale cache
php artisan optimize:clear

# Re-cache for production
php artisan config:cache
php artisan event:cache
php artisan route:cache
php artisan view:cache
