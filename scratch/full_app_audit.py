import os, re, subprocess

app_dir = r"c:\Users\Syed Nabeel\Downloads\Dunes-Laravel"

print("--- Running Full System Scan ---")

# 1. Check PHP syntax across all .php files
php_files = []
for root, dirs, files in os.walk(os.path.join(app_dir, "app")):
    for f in files:
        if f.endswith(".php"):
            php_files.append(os.path.join(root, f))

for root, dirs, files in os.walk(os.path.join(app_dir, "routes")):
    for f in files:
        if f.endswith(".php"):
            php_files.append(os.path.join(root, f))

syntax_errors = 0
for pf in php_files:
    res = subprocess.run(["php", "-l", pf], capture_output=True, text=True)
    if "No syntax errors detected" not in res.stdout:
        print(f"[SYNTAX ERROR] {pf}: {res.stdout}")
        syntax_errors += 1

print(f"PHP Syntax Check: {len(php_files)} files scanned. Errors: {syntax_errors}")

# 2. Check Blade View Compilation via php artisan view:cache
res_view = subprocess.run(["powershell", "-Command", "php artisan view:cache"], cwd=app_dir, capture_output=True, text=True)
print(f"Blade View Cache Output: {res_view.stdout.strip()} {res_view.stderr.strip()}")

# 3. Check Route List via php artisan route:list
res_route = subprocess.run(["powershell", "-Command", "php artisan route:list"], cwd=app_dir, capture_output=True, text=True)
if res_route.returncode == 0:
    print("Routes List: OK")
else:
    print(f"[ROUTE ERROR]: {res_route.stderr.strip()}")
