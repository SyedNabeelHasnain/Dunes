<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$compiler = app('blade.compiler');
$raw = file_get_contents(__DIR__ . '/../resources/views/partials/booking-modal.blade.php');
$compiled = $compiler->compileString($raw);

file_put_contents(__DIR__ . '/compiled_modal.php', $compiled);
echo "Compiled modal to compiled_modal.php (" . strlen($compiled) . " bytes)\n";

// Lint the compiled php
$cmd = 'php -l "' . __DIR__ . '/compiled_modal.php"';
system($cmd);
