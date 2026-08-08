with open(r"c:\Users\Syed Nabeel\Downloads\Dunes-Laravel\resources\views\tours\show.blade.php", "r", encoding="utf-8") as f:
    lines = f.readlines()

for idx, line in enumerate(lines, 1):
    if "thumb_image" in line or "hero_image" in line or "<img" in line:
        print(f"Line {idx}: {line.strip()}")
