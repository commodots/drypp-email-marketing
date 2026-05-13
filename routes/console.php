<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('smtp:reset')->daily();
Schedule::command('smtp:reset-hourly')->hourly();
Schedule::command('app:fetch-email-replies')->everyFiveMinutes();
Schedule::command('app:warmup-progress')->daily(); // Corrected command name
Schedule::command('inbox:health')->everyTenMinutes();
Schedule::command('app:reset-health-stats')->daily(); // Corrected command name
Schedule::command('spam:check')->everyFiveMinutes();