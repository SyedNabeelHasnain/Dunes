import os
import re

file_path = os.path.abspath(os.path.join(os.path.dirname(__file__), '../resources/views/index.blade.php'))
with open(file_path, 'r', encoding='utf-8') as f:
    lines = f.readlines()

if_stack = []
for idx, line in enumerate(lines, 1):
    # Find all blade directives
    directives = re.findall(r'@(if|elseif|else|endif)\b', line)
    for d in directives:
        if d == 'if':
            if_stack.append(idx)
            print(f"Line {idx:3d}: @if (depth {len(if_stack)}) -> {line.strip()[:60]}")
        elif d == 'endif':
            if if_stack:
                start_line = if_stack.pop()
                print(f"Line {idx:3d}: @endif (closes line {start_line}, remaining depth {len(if_stack)})")
            else:
                print(f"Line {idx:3d}: ERROR EXTRA @endif!")

print("\n--- UNCLOSED @if DIRECTIVES IN index.blade.php ---")
if if_stack:
    for line_num in if_stack:
        print(f"UNCLOSED @if at line {line_num}: {lines[line_num-1].strip()[:80]}")
else:
    print("ALL @if DIRECTIVES IN index.blade.php ARE PROPERLY CLOSED!")
