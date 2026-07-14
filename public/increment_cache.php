<?php
define('LARAVEL_START', microtime(true));

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

if (!isset($_GET['key']) || $_GET['key'] !== 'dunes2026') {
    die('Unauthorized');
}

try {
    $newVer = time();
    $updated = \App\Models\Setting::where('setting_key', 'cache_version')->update(['setting_value' => $newVer]);
    
    // Clear all configuration and cache
    \Illuminate\Support\Facades\Artisan::call('config:clear');
    \Illuminate\Support\Facades\Artisan::call('cache:clear');
    \Illuminate\Support\Facades\Artisan::call('view:clear');
    
    echo "SUCCESS: cache_version updated to $newVer (updated rows: $updated) and caches cleared successfully!";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
