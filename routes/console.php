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

// Tutup sesi expired setiap menit — cek jam tutup per sesi per sekolah
// Command ini yang buat alfa otomatis + poin pelanggaran
Schedule::command('attendance:close-expired-sessions')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/scheduler.log'));

// Backup: tutup semua sesi yang masih aktif jam 23:59 (safety net)
Schedule::command('attendance:close-daily-sessions')
    ->dailyAt('23:59')
    ->weekdays()
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/scheduler.log'));