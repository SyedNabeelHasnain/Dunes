import subprocess
import os

script_path = os.path.abspath(os.path.join(os.path.dirname(__file__), 'run_blade_compile.php'))
php_code = """<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\\Contracts\\Console\\Kernel::class);
$kernel->bootstrap();

use Illuminate\\Support\\Facades\\Blade;

try {
    $indexBlade = file_get_contents(__DIR__ . '/../resources/views/index.blade.php');
    $compiledIndex = Blade::compileString($indexBlade);
    file_put_contents(__DIR__ . '/compiled_index.php', $compiledIndex);
    
    $appBlade = file_get_contents(__DIR__ . '/../resources/views/layouts/app.blade.php');
    $compiledApp = Blade::compileString($appBlade);
    file_put_contents(__DIR__ . '/compiled_app.php', $compiledApp);

    echo "Blade compilation completed successfully!\n";
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
"""

with open(script_path, 'w', encoding='utf-8') as f:
    f.write(php_code)

res = subprocess.run(['php', script_path], capture_output=True, text=True)
print("STDOUT:", res.stdout)

for name in ['compiled_index.php', 'compiled_app.php']:
    fpath = os.path.join(os.path.dirname(__file__), name)
    if os.path.exists(fpath):
        lint = subprocess.run(['php', '-l', fpath], capture_output=True, text=True)
        print(f"LINT {name}: {lint.stdout.strip()}")
