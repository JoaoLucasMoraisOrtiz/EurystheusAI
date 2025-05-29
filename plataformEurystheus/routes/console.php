<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Security monitoring scheduled commands
Schedule::command('security:monitor')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/security-monitoring.log'));

// Security backup scheduled commands
Schedule::command('security:backup --type=auto')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/security-backups.log'));

// Weekly backup cleanup
Schedule::command('security:backup --cleanup')
    ->weekly()
    ->sundays()
    ->at('03:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/security-backups.log'));

// Daily backup verification (verify latest backup)
Schedule::command('security:backup --verify')
    ->dailyAt('04:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/security-backups.log'));
