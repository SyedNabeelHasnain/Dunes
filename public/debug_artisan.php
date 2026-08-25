<?php

if (!isset($_GET['key']) || $_GET['key'] !== 'dunes2026') {
    die("Unauthorized.");
}

define('LARAVEL_START', microtime(true));

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

// Bootstrap the console application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "<pre>";
echo "=== System Diagnostic ===\n";

if (isset($_GET['migrate'])) {
    echo "\n--- Running Migrations ---\n";
    try {
        Artisan::call('migrate', ['--force' => true]);
        echo Artisan::output();
    } catch (\Throwable $e) {
        echo "Migration Error: " . $e->getMessage() . "\n";
    }
}

if (isset($_GET['clear'])) {
    echo "\n--- Clearing Caches ---\n";
    Artisan::call('optimize:clear');
    echo Artisan::output();
}

echo "\n--- Database Tables & Columns ---\n";
try {
    $tables = ['bookings', 'contacts', 'booking_payments', 'users', 'settings'];
    foreach ($tables as $table) {
        if (Schema::hasTable($table)) {
            $cols = Schema::getColumnListing($table);
            echo "Table [{$table}]: " . implode(', ', $cols) . "\n";
        } else {
            echo "Table [{$table}]: MISSING!\n";
        }
    }
} catch (\Throwable $e) {
    echo "DB Check Error: " . $e->getMessage() . "\n";
}

echo "\n--- Recent Laravel Errors ---\n";
$logPath = storage_path('logs/laravel.log');
if (file_exists($logPath)) {
    $content = file_get_contents($logPath);
    preg_match_all('/\[\d{4}-\d{2}-\d{2} [^\]]+\] [^\n]+/', $content, $matches);
    $recent = array_slice($matches[0] ?? [], -20);
    foreach ($recent as $err) {
        echo htmlspecialchars($err) . "\n";
    }
} else {
    echo "No log file found at {$logPath}\n";
}

echo "\nDone!\n";

