<?php

if (($_GET['key'] ?? '') !== 'dunes2026') {
    die('Unauthorized');
}

if (file_exists(__DIR__ . '/../bootstrap/app.php')) {
    $app = require_once __DIR__ . '/../bootstrap/app.php';
} elseif (file_exists(__DIR__ . '/../dunes-laravel/bootstrap/app.php')) {
    $app = require_once __DIR__ . '/../dunes-laravel/bootstrap/app.php';
} else {
    die('Bootstrap app not found');
}

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "<pre>\n";
echo "=== SEEDING BLOGS DIRECTLY ON HOSTINGER ===\n";

try {
    \Illuminate\Support\Facades\Artisan::call('db:seed', [
        '--class' => 'BlogSeeder',
        '--force' => true
    ]);
    echo \Illuminate\Support\Facades\Artisan::output();
    echo "\nSUCCESS! Total blog posts now in DB: " . \App\Models\BlogPost::count() . "\n";
    foreach (\App\Models\BlogPost::all() as $p) {
        echo "- [#{$p->id}] {$p->title} (slug: {$p->slug})\n";
    }
} catch (\Throwable $e) {
    echo "ERROR SEEDING BLOGS: " . $e->getMessage() . "\n" . $e->getTraceAsString();
}

echo "</pre>\n";
