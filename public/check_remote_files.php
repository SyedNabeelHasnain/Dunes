<?php
if (!isset($_GET['key']) || $_GET['key'] !== 'dunes2026') {
    die('Unauthorized');
}

$baseDir = file_exists(__DIR__ . '/../dunes-laravel/.env') 
    ? __DIR__ . '/../dunes-laravel' 
    : __DIR__ . '/..';

echo "=== BASE DIR: $baseDir ===\n";

$appBlade = $baseDir . '/resources/views/layouts/app.blade.php';
$indexBlade = $baseDir . '/resources/views/index.blade.php';

echo "app.blade.php exists: " . (file_exists($appBlade) ? 'YES' : 'NO') . "\n";
if (file_exists($appBlade)) {
    $content = file_get_contents($appBlade);
    echo "app.blade.php lines: " . count(explode("\n", $content)) . "\n";
    echo "app.blade.php contains @@context: " . (strpos($content, '@@context') !== false ? 'YES' : 'NO') . "\n";
}

echo "index.blade.php exists: " . (file_exists($indexBlade) ? 'YES' : 'NO') . "\n";
if (file_exists($indexBlade)) {
    $content = file_get_contents($indexBlade);
    echo "index.blade.php lines: " . count(explode("\n", $content)) . "\n";
    echo "index.blade.php starts with renderReviewCardMarkup: " . (strpos($content, 'renderReviewCardMarkup') !== false ? 'YES' : 'NO') . "\n";
}
