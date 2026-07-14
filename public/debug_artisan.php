<?php

if (!isset($_GET['key']) || $_GET['key'] !== 'dunes2026') {
    die("Unauthorized.");
}

define('LARAVEL_START', microtime(true));

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->boot();

use Illuminate\Support\Facades\Artisan;

echo "<pre>";
echo "Clearing caches...\n";

try {
    Artisan::call('route:clear');
    echo "route:clear: " . Artisan::output() . "\n";
    
    Artisan::call('config:clear');
    echo "config:clear: " . Artisan::output() . "\n";
    
    Artisan::call('view:clear');
    echo "view:clear: " . Artisan::output() . "\n";
    
    Artisan::call('optimize:clear');
    echo "optimize:clear: " . Artisan::output() . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\nDone!\n";
