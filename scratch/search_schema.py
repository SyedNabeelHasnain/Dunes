import os, re

views_dir = r"c:\Users\Syed Nabeel\Downloads\Dunes-Laravel\resources\views"
keywords = ["BreadcrumbList", "itemListElement", "validFrom", "aggregateRating", "Product", "Offer", "ld+json"]

for root, dirs, files in os.walk(views_dir):
    for f in files:
        if f.endswith(".blade.php"):
            path = os.path.join(root, f)
            with open(path, "r", encoding="utf-8", errors="ignore") as file:
                content = file.read()
                matches = [kw for kw in keywords if kw in content]
                if matches:
                    rel_path = os.path.relpath(path, views_dir)
                    print(f"File: {rel_path} | Matches: {matches}")
