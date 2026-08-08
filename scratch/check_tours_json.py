import json

with open(r"c:\Users\Syed Nabeel\Downloads\Dunes-Laravel\database\seeders\data\tours.json", "r", encoding="utf-8") as f:
    data = json.load(f)

print("Total tours in tours.json:", len(data))
for t in data:
    print(f"ID: {t['id']}, Slug: {t['slug']}, Status: {t['status']}")
