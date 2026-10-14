<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

// Nightly aggregate reconciliation at 1:00 AM
Schedule::command('rollup:rebuild')->dailyAt('01:00');

// Scheduled automated executive report dispatches evaluated every hour
Schedule::command('reports:dispatch')->hourly();
