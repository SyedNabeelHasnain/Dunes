import subprocess

def test_file():
    cmd = """php -r "
    require 'vendor/autoload.php';
    $app = require_once 'bootstrap/app.php';
    $app->make(Illuminate\\\\Contracts\\\\Console\\\\Kernel::class)->bootstrap();
    $compiler = app('blade.compiler');
    $c = $compiler->compileString(file_get_contents('scratch/temp.blade.php'));
    file_put_contents('scratch/temp.php', $c);
    system('php -l scratch/temp.php');
    " """
    res = subprocess.run(cmd, shell=True, capture_output=True, text=True)
    return "No syntax errors detected" in res.stdout

with open('resources/views/tours/show.blade.php', 'r', encoding='utf-8') as f:
    lines = f.readlines()

for i in range(176, 327):
    test_lines = lines[:i] + lines[i+1:]
    with open('scratch/temp.blade.php', 'w', encoding='utf-8') as tmp:
        tmp.write("".join(test_lines))
    if test_file():
        print(f"Removing ONLY line {i+1}: {lines[i].strip()[:60]} FIXES THE BUG!")
