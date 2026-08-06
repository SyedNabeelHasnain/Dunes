<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Blade;

try {
    $indexBlade = file_get_contents(__DIR__ . '/../resources/views/index.blade.php');
    $compiledIndex = Blade::compileString($indexBlade);
    file_put_contents(__DIR__ . '/compiled_index.php', $compiledIndex);
    
    $appBlade = file_get_contents(__DIR__ . '/../resources/views/layouts/app.blade.php');
    $compiledApp = Blade::compileString($appBlade);
    file_put_contents(__DIR__ . '/compiled_app.php', $compiledApp);

    echo "Blade compilation completed successfully!
";
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "
";
}
