<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (($_GET['key'] ?? '') !== 'dunes2026') {
    die('Unauthorized');
}

echo "=== CHECKING HOSTINGER LOGS AND PERMISSIONS ===\n\n";

$possibleLogs = [
    __DIR__ . '/../dunes-laravel/storage/logs/laravel.log',
    __DIR__ . '/../storage/logs/laravel.log',
    '/home/u410503041/domains/dunesdiscoverytourism.com/dunes-laravel/storage/logs/laravel.log',
];

$found = false;
foreach ($possibleLogs as $logFile) {
    if (file_exists($logFile)) {
        echo "FOUND LOG FILE AT: {$logFile}\n";
        echo "Log Size: " . filesize($logFile) . " bytes\n";
        echo "Last 50 Lines of Laravel Log:\n";
        echo "-----------------------------------------------------\n";
        $lines = file($logFile);
        $last = array_slice($lines, -50);
        echo implode('', $last);
        echo "\n-----------------------------------------------------\n";
        $found = true;
        break;
    }
}

if (!$found) {
    echo "NO LOG FILE FOUND in any of:\n" . implode("\n", $possibleLogs) . "\n";
}

echo "\nTESTING BOOTSTRAP:\n";
try {
    $envFile = __DIR__ . '/../dunes-laravel/.env';
    echo ".env exists: " . (file_exists($envFile) ? 'YES' : 'NO') . "\n";
    $vendorFile = __DIR__ . '/../dunes-laravel/vendor/autoload.php';
    echo "vendor/autoload.php exists: " . (file_exists($vendorFile) ? 'YES' : 'NO') . "\n";
    
    if (file_exists($vendorFile)) {
        require $vendorFile;
        echo "Autoload required successfully.\n";
    }
    
    $appFile = __DIR__ . '/../dunes-laravel/bootstrap/app.php';
    echo "bootstrap/app.php exists: " . (file_exists($appFile) ? 'YES' : 'NO') . "\n";
    if (file_exists($appFile)) {
        $app = require $appFile;
        echo "App instantiated successfully.\n";
        $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
        $kernel->bootstrap();
        echo "Kernel bootstrapped successfully!\n";
    }
} catch (\Throwable $e) {
    echo "\nFATAL EXCEPTION DURING BOOTSTRAP:\n";
    echo "Type: " . get_class($e) . "\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
