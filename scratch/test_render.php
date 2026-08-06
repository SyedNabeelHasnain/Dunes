<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Clear compiled views
array_map('unlink', glob(storage_path('framework/views/*.php')));

try {
    $tour = new App\Models\Tour();
    $tour->id = 1;
    $tour->name = 'Evening Desert Safari Dubai';
    $tour->hero_image = 'evening-desert-safari-dubai.webp';
    $tour->short_desc = 'Short description';
    $tour->full_desc = 'Full description';
    $tour->rating = 4.9;
    $tour->review_count = 1247;
    $tour->setRelation('tiers', collect());
    $tour->setRelation('contentItems', collect());
    $tour->setRelation('itineraries', collect());

    $highlights = collect();
    $inclusions = collect();
    $exclusions = collect();
    $faqs = collect();
    $relatedTours = collect();

    $html = view('tours.show', compact('tour', 'highlights', 'inclusions', 'exclusions', 'faqs', 'relatedTours'))->render();
    echo "SUCCESS: Rendered " . strlen($html) . " bytes\n";
} catch (Throwable $e) {
    echo "EXCEPTIONAL ERROR: " . $e->getMessage() . "\n";
}

$files = glob(storage_path('framework/views/*.php'));
echo "Found " . count($files) . " cached view files:\n";
foreach ($files as $f) {
    echo "--- FILE: " . basename($f) . " ---\n";
    $cmd = 'php -l "' . $f . '"';
    system($cmd);
}
