import os
import re

views_dir = os.path.abspath(os.path.join(os.path.dirname(__file__), '../resources/views'))

for root, dirs, files in os.walk(views_dir):
    for file in files:
        if file.endswith('.blade.php'):
            full_path = os.path.join(root, file)
            with open(full_path, 'r', encoding='utf-8') as f:
                lines = f.readlines()
            
            if_balance = 0
            for idx, line in enumerate(lines, 1):
                ifs = len(re.findall(r'@if\b', line))
                endifs = len(re.findall(r'@endif\b', line))
                if_balance += (ifs - endifs)
            
            if if_balance != 0:
                print(f"MISMATCH IN {file}: @if balance = {if_balance}")

print("Done checking all views!")
