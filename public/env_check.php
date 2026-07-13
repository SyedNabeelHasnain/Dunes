<?php

header('Content-Type: text/plain');

// Secure token check
if (!isset($_GET['key']) || $_GET['key'] !== 'dunes2026') {
    header('HTTP/1.0 403 Forbidden');
    echo "Unauthorized.";
    exit;
}

$envPath = __DIR__ . '/../dunes-laravel/.env';
$examplePath = __DIR__ . '/../dunes-laravel/.env.example';
$logPath = __DIR__ . '/../dunes-laravel/storage/logs/laravel.log';

echo "--- ENV FILE STATUS ---\n";
if (!file_exists($envPath)) {
    echo ".env file does not exist. Attempting to copy from .env.example...\n";
    if (file_exists($examplePath)) {
        if (copy($examplePath, $envPath)) {
            echo "Successfully copied .env.example to .env.\n";
            // Set permissions
            chmod($envPath, 0640);
        } else {
            echo "ERROR: Failed to copy .env.example to .env.\n";
        }
    } else {
        echo "ERROR: .env.example not found at: $examplePath\n";
    }
} else {
    echo ".env file exists.\n";
}

// Ensure APP_KEY is generated and set
if (file_exists($envPath)) {
    $envContent = file_get_contents($envPath);
    if (strpos($envContent, 'APP_KEY=') === false || trim(explode("\n", explode('APP_KEY=', $envContent)[1])[0]) === '') {
        echo "APP_KEY is missing or empty. Generating new key...\n";
        $key = 'base64:' . base64_encode(random_bytes(32));
        
        if (strpos($envContent, 'APP_KEY=') !== false) {
            // Replace existing empty key
            $pattern = '/APP_KEY=[^\r\n]*/';
            $envContent = preg_replace($pattern, "APP_KEY=$key", $envContent);
        } else {
            // Append new key
            $envContent .= "\nAPP_KEY=$key\n";
        }
        
        if (file_put_contents($envPath, $envContent) !== false) {
            echo "Successfully generated and saved APP_KEY: $key\n";
        } else {
            echo "ERROR: Failed to write APP_KEY to .env.\n";
        }
    } else {
        echo "APP_KEY is already set.\n";
    }
}

// Check writable permissions on directories
echo "\n--- DIRECTORY PERMISSIONS ---\n";
$dirsToCheck = [
    __DIR__ . '/../dunes-laravel/storage',
    __DIR__ . '/../dunes-laravel/storage/framework',
    __DIR__ . '/../dunes-laravel/storage/framework/cache',
    __DIR__ . '/../dunes-laravel/storage/framework/sessions',
    __DIR__ . '/../dunes-laravel/storage/framework/views',
    __DIR__ . '/../dunes-laravel/storage/logs',
    __DIR__ . '/../dunes-laravel/bootstrap/cache',
];

foreach ($dirsToCheck as $dir) {
    if (file_exists($dir)) {
        $writable = is_writable($dir);
        $perms = substr(sprintf('%o', fileperms($dir)), -4);
        echo "$dir: " . ($writable ? "WRITABLE" : "NOT WRITABLE") . " (perms: $perms)\n";
        if (!$writable) {
            // Attempt to chmod
            @chmod($dir, 0775);
            echo " -> Attempted to set 0775. New status: " . (is_writable($dir) ? "WRITABLE" : "STILL NOT WRITABLE") . "\n";
        }
    } else {
        echo "$dir does not exist.\n";
    }
}

// Print the last few lines of the Laravel log file
echo "\n--- LAST 20 LINES OF LARAVEL LOG ---\n";
if (file_exists($logPath)) {
    $lines = file($logPath);
    $lastLines = array_slice($lines, -20);
    foreach ($lastLines as $line) {
        echo $line;
    }
} else {
    echo "laravel.log file not found or is empty.\n";
}
