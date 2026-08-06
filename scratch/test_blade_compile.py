import subprocess

cmd = """php -r "
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap();
try {
    $view = view('tours.show', [
        'tour' => App\\Models\\Tour::first(),
        'highlights' => collect(),
        'inclusions' => collect(),
        'exclusions' => collect(),
        'faqs' => collect(),
        'relatedTours' => collect()
    ])->render();
    echo 'SUCCESS! Rendered ' . strlen($view) . ' bytes';
} catch (Throwable $e) {
    echo 'ERROR: ' . $e->getMessage() . '\\n' . $e->getTraceAsString();
}
" """

res = subprocess.run(cmd, shell=True, capture_output=True, text=True)
print("STDOUT:", res.stdout)
print("STDERR:", res.stderr)
