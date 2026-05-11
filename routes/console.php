<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('smtp:reset')->daily();
Schedule::command('smtp:reset-hourly')->hourly();
Schedule::command('emails:fetch-replies')->everyFiveMinutes();
Schedule::command('warmup:progress')->daily();