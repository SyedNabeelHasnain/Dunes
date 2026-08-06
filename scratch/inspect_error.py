import re

with open('scratch/live_error.html', 'r', encoding='utf-8', errors='ignore') as f:
    html = f.read()

# Search for const markdown
idx = html.find('const markdown = ')
if idx != -1:
    end = html.find(';\n', idx)
    raw = html[idx+17:end]
    clean = raw.encode('utf-8').decode('unicode_escape')
    print("=== LIVE IGNITION ERROR ===")
    print(clean[:2000])
else:
    print("const markdown not found")
