<?php
if (!isset($_GET['key']) || $_GET['key'] !== 'dunes2026') {
    die('Unauthorized');
}

echo "=== REAL PATHS DIAGNOSTIC ===\n";
echo "__DIR__: " . __DIR__ . "\n";
echo "realpath(__DIR__): " . realpath(__DIR__) . "\n";

$candidates = [
    '/home/u410503041/domains/dunesdiscoverytourism.com/dunes-laravel',
    '/home/u410503041/dunes-laravel',
    '/home/u410503041/domains/dunesdiscoverytourism.com/public_html',
    '/home/u410503041/public_html',
    realpath(__DIR__ . '/..'),
    realpath(__DIR__ . '/../dunes-laravel'),
];

foreach (array_filter(array_unique($candidates)) as $dir) {
    echo "\n--- CHECKING DIRECTORY: $dir ---\n";
    $idx = $dir . '/resources/views/index.blade.php';
    $app = $dir . '/resources/views/layouts/app.blade.php';
    $viewsStorage = $dir . '/storage/framework/views';
    
    echo "index.blade.php exists: " . (file_exists($idx) ? 'YES' : 'NO') . "\n";
    if (file_exists($idx)) {
        $lines = file($idx);
        echo "  index.blade.php line count: " . count($lines) . "\n";
        echo "  index.blade.php line 1: " . trim($lines[0] ?? '') . "\n";
        echo "  index.blade.php line 348: " . trim($lines[347] ?? '') . "\n";
    }
    
    echo "app.blade.php exists: " . (file_exists($app) ? 'YES' : 'NO') . "\n";
    if (file_exists($app)) {
        $lines = file($app);
        echo "  app.blade.php line count: " . count($lines) . "\n";
        echo "  app.blade.php contains @@context: " . (strpos(file_get_contents($app), '@@context') !== false ? 'YES' : 'NO') . "\n";
    }

    echo "storage/framework/views exists: " . (is_dir($viewsStorage) ? 'YES' : 'NO') . "\n";
    if (is_dir($viewsStorage)) {
        $files = glob($viewsStorage . '/*.php');
        echo "  compiled views count: " . count($files) . "\n";
        foreach ($files as $f) {
            echo "  - " . basename($f) . " (" . date("Y-m-d H:i:s", filemtime($f)) . ")\n";
        }
    }
}
