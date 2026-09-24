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

// Automatically vacate forgotten / stale room sessions each night at 11:00 PM (building close)
Schedule::command('lab:close-stale-sessions')
    ->dailyAt('23:00')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/stale-sessions.log'));

// Dispatch daily utilization summary (rooms, tools, software) to Telegram group at 7:00 PM
Schedule::command('lab:send-telegram-summary --date=today')
    ->dailyAt('19:00')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/telegram-daily-summary.log'));

