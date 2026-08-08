<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    echo "Compiling tours.index...\n";
    $v1 = view('tours.index', [
        'categories' => collect(),
        'tours' => collect(),
        'selectedCategorySlug' => null
    ])->render();
    echo "tours.index compiled successfully! Length: " . strlen($v1) . "\n";
} catch (\Throwable $e) {
    echo "ERROR in tours.index: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}

try {
    echo "Compiling tours.show...\n";
    $tour = new \App\Models\Tour([
        'id' => 1,
        'name' => 'Test Tour',
        'slug' => 'test-tour',
        'rating' => 4.9,
        'review_count' => 100,
        'short_desc' => 'Short desc',
        'full_desc' => 'Full desc',
        'duration' => '3 hours',
        'pickup_time' => '7am',
        'dropoff_time' => '10am',
        'hero_image' => 'test.avif'
    ]);
    $tour->setRelation('contentItems', collect());
    $tour->setRelation('itineraries', collect());
    $tour->setRelation('tiers', collect());
    $tour->setRelation('addons', collect());

    $highlights = collect();
    $inclusions = collect();
    $exclusions = collect();
    $faqs = collect();
    $v2 = view('tours.show', compact('tour', 'highlights', 'inclusions', 'exclusions', 'faqs'))->render();
    echo "tours.show compiled successfully! Length: " . strlen($v2) . "\n";
} catch (\Throwable $e) {
    echo "ERROR in tours.show: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
