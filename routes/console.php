<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Dispatch post-tour review collection requests to yesterday's completed safari guests daily at 10:00 AM
Schedule::command('tours:send-review-requests')->dailyAt('10:00');

// Recover abandoned booking checkouts created in the last 24 hours (hourly frequency)
Schedule::command('bookings:recover-abandoned')->hourly();
