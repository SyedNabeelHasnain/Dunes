with open(r"c:\Users\Syed Nabeel\Downloads\Dunes-Laravel\resources\views\tours\show.blade.php", "r", encoding="utf-8") as f:
    lines = f.readlines()

for i, line in enumerate(lines, 1):
    if "ld+json" in line:
        print(f"Line {i}: {line.strip()}")
