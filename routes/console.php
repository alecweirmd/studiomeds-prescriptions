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

Schedule::command('intakes:abandoned-report')
    ->dailyAt('09:00')
    ->timezone('America/New_York')
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command('outreach:post-approval-followups')
    ->dailyAt('09:00')
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command('outreach:facebook-review-requests')
    ->dailyAt('09:15')
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command('outreach:rejection-reengagements')
    ->dailyAt('09:30')
    ->withoutOverlapping()
    ->onOneServer();
