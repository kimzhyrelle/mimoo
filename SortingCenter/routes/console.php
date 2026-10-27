<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('checklist:reset')->dailyAt('06:00');
Schedule::command('checklist:reset')->dailyAt('14:00');
Schedule::command('checklist:reset')->dailyAt('22:00');
