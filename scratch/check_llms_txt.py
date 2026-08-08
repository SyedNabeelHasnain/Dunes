import os

base_dir = r"c:\Users\Syed Nabeel\Downloads\Dunes-Laravel"
llms_path = os.path.join(base_dir, "public", "llms.txt")
llms_full_path = os.path.join(base_dir, "public", "llms-full.txt")

print("llms.txt exists:", os.path.exists(llms_path))
print("llms-full.txt exists:", os.path.exists(llms_full_path))
