<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Account Lifecycle: permanently purge accounts whose 60-day
// deletion grace period has elapsed (runs daily at 3:00 AM).
Schedule::command('accounts:purge-expired')->dailyAt('03:00');
