import os
import subprocess

file_path = os.path.abspath(os.path.join(os.path.dirname(__file__), '../resources/views/tours/show.blade.php'))

with open(file_path, 'r', encoding='utf-8') as f:
    lines = f.readlines()

def test_lines(subset_lines):
    content = "".join(subset_lines)
    cmd = """php -r "
    require 'vendor/autoload.php';
    $app = require_once 'bootstrap/app.php';
    $app->make(Illuminate\\\\Contracts\\\\Console\\\\Kernel::class)->bootstrap();
    $compiler = app('blade.compiler');
    $c = $compiler->compileString(file_get_contents('scratch/temp.blade.php'));
    file_put_contents('scratch/temp.php', $c);
    system('php -l scratch/temp.php');
    " """
    with open('scratch/temp.blade.php', 'w', encoding='utf-8') as tmp:
        tmp.write(content)
    res = subprocess.run(cmd, shell=True, capture_output=True, text=True)
    return "No syntax errors detected" in res.stdout

print("Full file test:", test_lines(lines))

# Binary search / line elimination
for i in range(0, len(lines), 20):
    chunk_test = lines[:i] + lines[i+20:]
    if test_lines(chunk_test):
        print(f"Removing lines {i+1} to {i+20} FIXES the error!")
