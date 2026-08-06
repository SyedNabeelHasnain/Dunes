<?php

// Secure token check
if (!isset($_GET['key']) || $_GET['key'] !== 'dunes2026') {
    header('HTTP/1.0 403 Forbidden');
    echo "Unauthorized access.";
    exit;
}

$coreZip = __DIR__ . '/../dunes-laravel/core.zip';
$publicZip = __DIR__ . '/public.zip';

$extractedCore = false;
$extractedPublic = false;

// 1. Extract Core Laravel Files
if (file_exists($coreZip)) {
    $zip = new ZipArchive;
    if ($zip->open($coreZip) === TRUE) {
        $zip->extractTo(__DIR__ . '/../dunes-laravel/');
        $zip->close();
        unlink($coreZip);
        $extractedCore = true;
    } else {
        echo "Failed to open core.zip\n";
    }
} else {
    echo "core.zip not found at: $coreZip\n";
}

// 2. Extract Public Files
if (file_exists($publicZip)) {
    $zip = new ZipArchive;
    if ($zip->open($publicZip) === TRUE) {
        $zip->extractTo(__DIR__);
        $zip->close();
        unlink($publicZip);
        $extractedPublic = true;
    } else {
        echo "Failed to open public.zip\n";
    }
} else {
    echo "public.zip not found at: $publicZip\n";
}

if ($extractedCore || $extractedPublic) {
    echo "SUCCESS: Extraction completed successfully!\n";
    
    // Purge compiled storage views
    $possibleViewsDirs = [
        __DIR__ . '/../storage/framework/views',
        __DIR__ . '/../dunes-laravel/storage/framework/views',
        '/home/u410503041/domains/dunesdiscoverytourism.com/storage/framework/views',
        '/home/u410503041/domains/dunesdiscoverytourism.com/dunes-laravel/storage/framework/views',
    ];
    foreach (array_unique($possibleViewsDirs) as $vDir) {
        if (is_dir($vDir)) {
            $files = glob($vDir . '/*');
            foreach ($files as $file) {
                if (is_file($file) && basename($file) !== '.gitignore') {
                    @unlink($file);
                }
            }
        }
    }

    if (function_exists('opcache_reset')) {
        @opcache_reset();
    }
} else {
    echo "ERROR: Extraction failed. Core: " . ($extractedCore ? 'YES' : 'NO') . ", Public: " . ($extractedPublic ? 'YES' : 'NO') . "\n";
}
