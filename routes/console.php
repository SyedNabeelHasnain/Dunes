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

// Dispatch post-tour review collection requests to yesterday's completed safari guests daily at 10:00 AM Dubai time
Schedule::command('tours:send-review-requests')->dailyAt('10:00')->timezone('Asia/Dubai')->withoutOverlapping(30);

// Recover abandoned booking checkouts created in the last 24 hours (hourly frequency)
Schedule::command('bookings:recover-abandoned')->hourly()->withoutOverlapping(30);

// Process queued background jobs (e.g. Meta CAPI, notifications, recovery) every minute
Schedule::command('queue:work --stop-when-empty --tries=3')->everyMinute()->withoutOverlapping(5);

// Synchronize foreign currency exchange rates (USD, EUR, GBP, SAR, INR) daily at 02:00 AM Dubai time
Schedule::command('currency:sync-rates')->dailyAt('02:00')->timezone('Asia/Dubai')->withoutOverlapping(30);

// Dispatch scheduled email marketing campaigns (every 5 minutes)
Schedule::command('campaigns:send-scheduled')->everyFiveMinutes()->withoutOverlapping(15);

// Synchronize customer reviews and rating metrics from Google Places API daily at midnight Dubai time
Schedule::command('reviews:sync-google')->daily()->timezone('Asia/Dubai')->withoutOverlapping(30);

// Synchronize customer reviews and rating metrics from TripAdvisor Content API daily at 00:30 AM Dubai time
Schedule::command('reviews:sync-tripadvisor')->dailyAt('00:30')->timezone('Asia/Dubai')->withoutOverlapping(30);

// Audit and verify accessibility of customer review media gallery links weekly on Mondays at 03:00 AM Dubai time
Schedule::command('gallery:verify-media')->weeklyOn(1, '03:00')->timezone('Asia/Dubai')->withoutOverlapping(60);

// Audit and verify health, anchor text, and HTTP status of registered backlinks daily at 04:00 AM Dubai time
Schedule::command('seo:verify-backlinks')->dailyAt('04:00')->timezone('Asia/Dubai')->withoutOverlapping(30);

// Stream recently modified catalog URLs via IndexNow daily at 04:30 AM Dubai time (complying with Bing Webmaster streaming recommendation)
Schedule::command('seo:indexnow --recent')->dailyAt('04:30')->timezone('Asia/Dubai')->withoutOverlapping(30);

// Prune expired session recordings and heatmaps daily according to retention settings at 03:30 AM Dubai time
Schedule::command('telemetry:purge-recordings')->dailyAt('03:30')->timezone('Asia/Dubai')->withoutOverlapping(30);

// Enforce privacy consent log & RUM vitals data retention policy daily at 03:45 AM Dubai time
Schedule::command('privacy:purge-expired')->dailyAt('03:45')->timezone('Asia/Dubai')->withoutOverlapping(30);

// Automated daily incremental & weekly comprehensive Website Audit scans
Schedule::command('audit:run --type=incremental')->dailyAt('05:00')->timezone('Asia/Dubai')->withoutOverlapping(30);
Schedule::command('audit:run --type=full')->weeklyOn(0, '05:30')->timezone('Asia/Dubai')->withoutOverlapping(60);

