import json, os

data_dir = r"c:\Users\Syed Nabeel\Downloads\Dunes-Laravel\database\seeders\data"

with open(os.path.join(data_dir, "blog_posts.json"), "r", encoding="utf-8") as f:
    posts = json.load(f)

with open(os.path.join(data_dir, "tours.json"), "r", encoding="utf-8") as f:
    tours = json.load(f)

print("=== EXISTING TOURS ===")
for t in tours:
    print(f"- Tour ID {t['id']}: {t['name']} (slug: {t['slug']})")

print("\n=== EXISTING BLOG POSTS ===")
for p in posts:
    print(f"- Post ID {p['id']}: {p['title']} (slug: {p['slug']}) | Category ID: {p.get('category_id')}")
