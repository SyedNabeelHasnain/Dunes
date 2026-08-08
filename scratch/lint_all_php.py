import os
import subprocess

base_dir = r"c:\Users\Syed Nabeel\Downloads\Dunes-Laravel"
dirs_to_check = ["app", "routes", "database", "config"]

php_files = []
for d in dirs_to_check:
    full_d = os.path.join(base_dir, d)
    for root, dirs, files in os.walk(full_d):
        for f in files:
            if f.endswith(".php"):
                php_files.append(os.path.join(root, f))

print(f"Linting {len(php_files)} PHP files with 'php -l'...")

errors = []
for pf in php_files:
    res = subprocess.run(["php", "-l", pf], capture_output=True, text=True)
    if res.returncode != 0:
        errors.append(f"[{os.path.relpath(pf, base_dir)}] LINT ERROR: {res.stdout.strip()}")

print(f"\nPHP Lint Check complete!")
print(f"Total files checked: {len(php_files)}")
print(f"Total syntax errors detected: {len(errors)}")
for err in errors:
    print(" -", err)
