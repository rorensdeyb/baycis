#!/bin/bash
# Railway cron service: runs the Laravel scheduler every minute
# (e.g. accounts:purge-expired daily at 03:00).
# Dashboard → Cron service → Settings → Deploy → Custom Start Command:
#   chmod +x ./railway/run-cron.sh && sh ./railway/run-cron.sh
# Make executable locally: chmod +x railway/run-cron.sh

# This block of code runs the Laravel scheduler every minute
while [ true ]
    do
        echo "Running the scheduler..."
        php artisan schedule:run --verbose --no-interaction &
        sleep 60
    done
