import subprocess

with open('resources/views/tours/show.blade.php', 'r', encoding='utf-8') as f:
    lines = f.readlines()

# Test removing line 176
test_lines = lines[:175] + lines[176:]
with open('scratch/temp.blade.php', 'w', encoding='utf-8') as tmp:
    tmp.write("".join(test_lines))

cmd = 'php -r "require \'vendor/autoload.php\'; $app=require \'bootstrap/app.php\'; $app->make(Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap(); $c=app(\'blade.compiler\')->compileString(file_get_contents(\'scratch/temp.blade.php\')); file_put_contents(\'scratch/temp.php\', $c); system(\'php -l scratch/temp.php\');"'
res = subprocess.run(cmd, shell=True, capture_output=True, text=True)
print("STDOUT:", res.stdout)
print("STDERR:", res.stderr)
