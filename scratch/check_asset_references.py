import os, re

public_dir = r"c:\Users\Syed Nabeel\Downloads\Dunes-Laravel\public"
views_dir = r"c:\Users\Syed Nabeel\Downloads\Dunes-Laravel\resources\views"

missing_assets = []
scanned = 0

pattern = re.compile(r"asset\(['\"]([^'\"]+)['\"]\)")

for root, dirs, files in os.walk(views_dir):
    for f in files:
        if f.endswith(".blade.php"):
            path = os.path.join(root, f)
            with open(path, "r", encoding="utf-8", errors="ignore") as file:
                content = file.read()
                matches = pattern.findall(content)
                for m in matches:
                    if "{{" not in m and "}}" not in m and "$" not in m:
                        scanned += 1
                        asset_path = os.path.join(public_dir, m.replace("/", os.sep))
                        if not os.path.exists(asset_path):
                            missing_assets.append((f, m))

print(f"Scanned {scanned} static asset() references.")
if missing_assets:
    print(f"Found {len(missing_assets)} missing static asset references:")
    for view, asset in missing_assets:
        print(f"  In {view}: {asset}")
else:
    print("100% of static asset references exist on disk!")
