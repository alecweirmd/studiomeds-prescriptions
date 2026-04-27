<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command('patients:cleanup')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command('intakes:mark-abandoned')
    ->dailyAt('00:00')
    ->withoutOverlapping()
    ->onOneServer();
