import json

path = r"c:\Users\Syed Nabeel\Downloads\Dunes-Laravel\database\seeders\data\tour_tiers.json"
with open(path, "r", encoding="utf-8") as f:
    data = json.load(f)

for tt in data:
    if tt["id"] == 42:
        tt["tier_id"] = 4
        print("Updated tt 42 tier_id from 3 to 4")

with open(path, "w", encoding="utf-8") as f:
    json.dump(data, f, indent=4)
