<?php

use Illuminate\Support\Facades\Schedule;

/**
 * SiManS - Scheduled Tasks
 *
 * Development: php artisan schedule:work
 * Production cron: * * * * * cd /path && php artisan schedule:run >> /dev/null 2>&1
 */

// Buat sesi absensi otomatis setiap hari jam 06:00 (Senin-Sabtu)
Schedule::command('attendance:create-daily-sessions')
    ->dailyAt('06:00')
    ->weekdays()
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/scheduler.log'));

// Tutup sesi + alfa siswa yang tidak absen jam 08:00
Schedule::command('attendance:close-daily-sessions')
    ->dailyAt('08:00')
    ->weekdays()
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/scheduler.log'));
