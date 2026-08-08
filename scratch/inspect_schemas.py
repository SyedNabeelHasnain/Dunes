import os, re

views_dir = r"c:\Users\Syed Nabeel\Downloads\Dunes-Laravel\resources\views"

for root, dirs, files in os.walk(views_dir):
    for f in files:
        if f.endswith(".blade.php"):
            path = os.path.join(root, f)
            with open(path, "r", encoding="utf-8", errors="ignore") as file:
                content = file.read()
                matches = re.findall(r'<script type="application/ld\+json">(.*?)</script>', content, re.DOTALL)
                if matches:
                    rel_path = os.path.relpath(path, views_dir)
                    print(f"=== File: {rel_path} ({len(matches)} schemas) ===")
                    for idx, m in enumerate(matches, 1):
                        print(f"Schema #{idx}:\n{m.strip()}\n")
