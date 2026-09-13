#!/bin/bash
# Railway worker service: processes queued jobs (QUEUE_CONNECTION=database).
# Dashboard → Worker service → Settings → Deploy → Custom Start Command:
#   chmod +x ./railway/run-worker.sh && sh ./railway/run-worker.sh
# Make executable locally: chmod +x railway/run-worker.sh

# This command runs the queue worker.
php artisan queue:work --sleep=3 --tries=3
