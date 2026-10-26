<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Record scheduler heartbeat timestamp every minute so health checks can confirm active cron execution
Schedule::call(function () {
    \Illuminate\Support\Facades\Cache::put('scheduler_last_heartbeat', now()->toIso8601String(), 86400);
})->everyMinute()->name('scheduler:heartbeat');

// Dispatch post-tour review collection requests to yesterday's completed safari guests daily at 10:00 AM
Schedule::command('tours:send-review-requests')->dailyAt('10:00')->withoutOverlapping(10);

// Recover abandoned booking checkouts created in the last 24 hours (hourly frequency)
Schedule::command('bookings:recover-abandoned')->hourly()->withoutOverlapping(10);

// Process queued background jobs (e.g. Meta CAPI, notifications, recovery) every minute
Schedule::command('queue:work --stop-when-empty --tries=3')->everyMinute()->withoutOverlapping(5);

// Synchronize foreign currency exchange rates (USD, EUR, GBP, SAR, INR) daily at 02:00 AM
Schedule::command('currency:sync-rates')->dailyAt('02:00')->withoutOverlapping(10);

// Dispatch scheduled email marketing campaigns (every 5 minutes)
Schedule::command('campaigns:send-scheduled')->everyFiveMinutes()->withoutOverlapping(10);

// Synchronize customer reviews and rating metrics from Google Places API daily at midnight
Schedule::command('reviews:sync-google')->daily()->withoutOverlapping()->runInBackground();

// Synchronize customer reviews and rating metrics from TripAdvisor Content API daily at 00:30 AM
Schedule::command('reviews:sync-tripadvisor')->dailyAt('00:30')->withoutOverlapping()->runInBackground();

// Audit and verify accessibility of customer review media gallery links weekly on Mondays at 03:00 AM
Schedule::command('gallery:verify-media')->weeklyOn(1, '03:00')->withoutOverlapping()->runInBackground();
