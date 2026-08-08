with open(r"c:\Users\Syed Nabeel\Downloads\Dunes-Laravel\resources\views\admin\tours\edit.blade.php", "r", encoding="utf-8") as f:
    lines = f.readlines()

for idx, line in enumerate(lines, 1):
    if "<img" in line:
        print(f"Line {idx}: {line.strip()}")
