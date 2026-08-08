import json

path = r"c:\Users\Syed Nabeel\Downloads\Dunes-Laravel\database\seeders\data\tour_addons.json"
with open(path, "r", encoding="utf-8") as f:
    data = json.load(f)

print("Total tour_addons:", len(data))
print("Addons:", data)
