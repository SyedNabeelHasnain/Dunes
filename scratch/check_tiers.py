import json

with open(r"c:\Users\Syed Nabeel\Downloads\Dunes-Laravel\database\seeders\data\tour_tiers.json", "r", encoding="utf-8") as f:
    data = json.load(f)

for tt in data:
    if tt["tour_id"] in (4, 8):
        print(tt)
