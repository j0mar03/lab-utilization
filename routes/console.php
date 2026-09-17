<?php

use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Schedule — Lab Utilization System
|--------------------------------------------------------------------------
|
| To activate the scheduler on your server, add ONE cron entry:
|
|   * * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1
|
| On shared hosting (cPanel): Cron Jobs → add the line above.
| This single cron runs every minute; Laravel handles the timing internally.
|
*/

// Check for overdue transactions every 15 minutes and send Telegram alerts
Schedule::command('lab:check-overdue')
    ->everyFifteenMinutes()
    ->withoutOverlapping()    // skip if previous run is still going
    ->runInBackground()       // non-blocking
    ->appendOutputTo(storage_path('logs/overdue-check.log'));
