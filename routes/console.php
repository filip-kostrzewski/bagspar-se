<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('bagspar:send-access-reminders')->dailyAt('08:00')->timezone('Europe/Stockholm');
Schedule::command('bagspar:send-onboarding-nudges')->dailyAt('09:00')->timezone('Europe/Stockholm');
Schedule::command('model:prune')->daily();
