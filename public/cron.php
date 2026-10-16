<?php

/**
 * Dunes Discovery Tourism - Cron Runner
 *
 * Supports execution via:
 * 1. Hostinger "PHP" mode: domains/dunesdiscoverytourism.com/public_html/cron.php
 * 2. Hostinger "Custom" mode: php /home/u410503041/domains/dunesdiscoverytourism.com/public_html/cron.php
 * 3. HTTP / Webhook: https://dunesdiscoverytourism.com/cron.php?token=dunes_cron_2026
 */

$isCli = (php_sapi_name() === 'cli');

if (!$isCli) {
    $token = $_GET['token'] ?? $_POST['token'] ?? '';
    $validToken = getenv('CRON_SECRET_TOKEN') ?: 'dunes_cron_2026';

    if (empty($token) || !hash_equals($validToken, (string) $token)) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized cron token.']);
        exit(1);
    }
}

// Locate core Laravel application root
$coreBase = file_exists(__DIR__ . '/../dunes-laravel/bootstrap/app.php')
    ? __DIR__ . '/../dunes-laravel'
    : __DIR__ . '/..';

if (!file_exists($coreBase . '/bootstrap/app.php')) {
    fwrite(STDERR, "Error: Unable to locate Laravel core bootstrap in {$coreBase}.\n");
    exit(1);
}

require $coreBase . '/vendor/autoload.php';

/** @var \Illuminate\Foundation\Application $app */
$app = require_once $coreBase . '/bootstrap/app.php';

// Execute the Laravel scheduler
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$status = $kernel->call('schedule:run');
$output = trim($kernel->output());

// Record heartbeat in cache
\Illuminate\Support\Facades\Cache::put('scheduler_last_heartbeat', now()->toIso8601String(), 86400);

if ($isCli) {
    echo "Scheduler executed successfully at " . now()->toIso8601String() . ".\n";
    if (!empty($output)) {
        echo $output . "\n";
    }
    exit($status);
} else {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'success',
        'cron_active' => true,
        'server_time' => now()->toIso8601String(),
        'heartbeat' => now()->toIso8601String(),
        'output' => $output ?: 'No scheduled tasks needed to run right now.',
        'message' => 'Scheduler executed successfully.',
    ]);
    exit(0);
}
