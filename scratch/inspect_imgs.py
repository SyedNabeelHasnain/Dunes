import os
import re

base_dir = r"c:\Users\Syed Nabeel\Downloads\Dunes-Laravel"
files = [
    r"resources\views\index.blade.php",
    r"resources\views\admin\blogs\edit.blade.php",
    r"resources\views\admin\blogs\index.blade.php",
    r"resources\views\admin\tours\edit.blade.php",
    r"resources\views\tours\index.blade.php",
    r"resources\views\tours\show.blade.php"
]

for rel in files:
    full = os.path.join(base_dir, rel)
    with open(full, "r", encoding="utf-8", errors="ignore") as f:
        content = f.read()
    img_matches = re.findall(r'<img\b[^>]*>', content, re.IGNORECASE | re.DOTALL)
    for img in img_matches:
        if 'alt=' not in img.lower():
            print(f"[{rel}] MISSING ALT:\n{img}\n---")
        else:
            print(f"[{rel}] HAS ALT OK:\n{img[:80]}...\n---")
