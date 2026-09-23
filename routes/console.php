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

// Process queued background jobs (e.g. Meta CAPI, notifications, recovery) every minute
Schedule::command('queue:work --stop-when-empty --tries=3')->everyMinute();

// Synchronize foreign currency exchange rates (USD, EUR, GBP, SAR, INR) daily at 02:00 AM
Schedule::command('currency:sync-rates')->dailyAt('02:00');

// Dispatch scheduled email marketing campaigns (every 5 minutes)
Schedule::command('campaigns:send-scheduled')->everyFiveMinutes();
