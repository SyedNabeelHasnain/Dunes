<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Blade;

$views = glob(__DIR__ . '/../resources/views/**/*.blade.php');
$views = array_merge($views, glob(__DIR__ . '/../resources/views/*.blade.php'));
$views = array_unique($views);

echo "Found " . count($views) . " view files.
";

$errors = [];
foreach ($views as $v) {
    try {
        $content = file_get_contents($v);
        $compiled = Blade::compileString($content);
        
        $tmpFile = sys_get_temp_dir() . '/test_compile_' . md5($v) . '.php';
        file_put_contents($tmpFile, $compiled);
        
        $output = [];
        $returnVar = 0;
        exec("php -l " . escapeshellarg($tmpFile) . " 2>&1", $output, $returnVar);
        @unlink($tmpFile);
        
        if ($returnVar !== 0) {
            $errors[$v] = implode("
", $output);
        }
    } catch (Throwable $e) {
        $errors[$v] = "EXCEPTION: " . $e->getMessage();
    }
}

if (empty($errors)) {
    echo "SUCCESS: ALL " . count($views) . " VIEW FILES COMPILED AND LINTED WITH 0 SYNTAX ERRORS!
";
} else {
    echo "FAILED: " . count($errors) . " VIEW FILES HAD SYNTAX ERRORS:
";
    foreach ($errors as $view => $err) {
        echo "
VIEW: $view
ERROR: $err
";
    }
}
