import os
import re

file_path = os.path.abspath(os.path.join(os.path.dirname(__file__), '../resources/views/partials/booking-modal.blade.php'))
with open(file_path, 'r', encoding='utf-8') as f:
    lines = f.readlines()

directives_pattern = re.compile(r'@(if|elseif|else|endif|unless|endunless|isset|endisset|empty|endempty)\b')

stack = []
for idx, line in enumerate(lines, 1):
    matches = directives_pattern.findall(line)
    for d in matches:
        if d in ('if', 'unless', 'isset', 'empty'):
            stack.append((d, idx))
            print(f"Line {idx:3d}: @{d:<7} (depth {len(stack)}) -> {line.strip()[:70]}")
        elif d in ('endif', 'endunless', 'endisset', 'endempty'):
            if stack:
                start_type, start_line = stack.pop()
                print(f"Line {idx:3d}: @{d:<7} (closes @{start_type} line {start_line}, remaining depth {len(stack)})")
            else:
                print(f"Line {idx:3d}: ERROR EXTRA @{d}!")

print("\n=== UNCLOSED DIRECTIVES IN booking-modal.blade.php ===")
for dtype, lnum in stack:
    print(f"UNCLOSED @{dtype} at line {lnum}: {lines[lnum-1].strip()[:90]}")
