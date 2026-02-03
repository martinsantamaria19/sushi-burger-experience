<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Programar verificación de suscripciones expiradas diariamente a las 2:00 AM
Schedule::command('subscriptions:check-expiry')
    ->dailyAt('02:00')
    ->timezone('America/Montevideo')
    ->withoutOverlapping()
    ->runInBackground();
