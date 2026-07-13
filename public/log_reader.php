<?php

header('Content-Type: text/plain');

if (!isset($_GET['key']) || $_GET['key'] !== 'dunes2026') {
    die("Unauthorized.");
}

$logPath = __DIR__ . '/../dunes-laravel/storage/logs/laravel.log';

echo "--- LARAVEL LOG FILES ---\n";
if (file_exists($logPath)) {
    echo "Found log file. Reading last 50 lines:\n\n";
    $lines = file($logPath);
    $lastLines = array_slice($lines, -50);
    foreach ($lastLines as $line) {
        echo $line;
    }
} else {
    echo "laravel.log file not found at: $logPath\n";
    // Check if storage directory exists
    $storageDir = __DIR__ . '/../dunes-laravel/storage';
    if (file_exists($storageDir)) {
        echo "Storage directory exists.\n";
        // List files in storage/logs
        $logsDir = $storageDir . '/logs';
        if (file_exists($logsDir)) {
            echo "Logs directory exists. Files inside:\n";
            print_r(scandir($logsDir));
        } else {
            echo "Logs directory does not exist.\n";
        }
    } else {
        echo "dunes-laravel/storage directory does not exist.\n";
    }
}

// Check PHP extensions required by Laravel
echo "\n--- PHP EXTENSIONS ---\n";
$extensions = ['pdo_mysql', 'openssl', 'mbstring', 'xml', 'curl', 'gd', 'zip'];
foreach ($extensions as $ext) {
    echo "$ext: " . (extension_loaded($ext) ? "LOADED" : "NOT LOADED") . "\n";
}

// Print directory status
echo "\n--- DIRECTORY STATUS ---\n";
echo "bootstrap/cache: " . (is_writable(__DIR__ . '/../dunes-laravel/bootstrap/cache') ? 'Writable' : 'Not Writable') . "\n";
echo "storage: " . (is_writable(__DIR__ . '/../dunes-laravel/storage') ? 'Writable' : 'Not Writable') . "\n";
