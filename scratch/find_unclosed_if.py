import os
import re

file_path = os.path.abspath(os.path.join(os.path.dirname(__file__), '../resources/views/layouts/app.blade.php'))

with open(file_path, 'r', encoding='utf-8') as f:
    lines = f.readlines()

if_count = 0
for idx, line in enumerate(lines, 1):
    ifs = len(re.findall(r'@if\b', line))
    endifs = len(re.findall(r'@endif\b', line))
    if_count += (ifs - endifs)
    if ifs > 0 or endifs > 0:
        print(f"Line {idx:3d} [diff {ifs-endifs:+d}] [balance {if_count:2d}]: {line.strip()[:80]}")

print(f"\nFinal @if balance in app.blade.php: {if_count}")
