import os
import re
import glob

base_dir = r"c:\Users\Syed Nabeel\Downloads\Dunes-Laravel"
views_dir = os.path.join(base_dir, "resources", "views")
public_dir = os.path.join(base_dir, "public")

print("=== 1. AUDITING ALL BLADE VIEWS ===")
blade_files = []
for root, dirs, files in os.walk(views_dir):
    for f in files:
        if f.endswith(".blade.php"):
            blade_files.append(os.path.join(root, f))

print(f"Found {len(blade_files)} Blade template files.")

issues = []

for bf in blade_files:
    rel_path = os.path.relpath(bf, base_dir)
    with open(bf, "r", encoding="utf-8", errors="ignore") as f:
        content = f.read()
    
    # Check for img without alt attribute
    img_matches = re.findall(r'<img\s+[^>]+>', content, re.IGNORECASE | re.DOTALL)
    for img in img_matches:
        if 'alt=' not in img.lower():
            issues.append(f"[{rel_path}] <img> tag missing alt attribute: {re.sub(r'\\s+', ' ', img)[:80]}")
            
    # Check for forms without @csrf
    form_matches = re.findall(r'<form\s+[^>]*method=["\']post["\'][^>]*>', content, re.IGNORECASE)
    for form in form_matches:
        # Check surrounding block for @csrf
        form_idx = content.find(form)
        form_block = content[form_idx:form_idx+500]
        if '@csrf' not in form_block and 'csrf_token' not in form_block and '_token' not in form_block:
            issues.append(f"[{rel_path}] POST <form> missing @csrf token: {form[:60]}")
            
    # Check for asset references to images/ and verify existence in public/
    asset_imgs = re.findall(r"asset\(['\"]images/([^'\"]+)['\"]\)", content)
    for img_file in asset_imgs:
        # Ignore dynamic PHP strings
        if '$' in img_file or '{' in img_file or '?>' in img_file:
            continue
        real_img_path = os.path.join(public_dir, "images", img_file.replace("/", os.sep))
        if not os.path.exists(real_img_path):
            # Check if .avif / .webp version exists
            alt_img = re.sub(r'\.(jpg|jpeg|png|webp)$', '.avif', real_img_path, flags=re.IGNORECASE)
            if not os.path.exists(alt_img):
                issues.append(f"[{rel_path}] Referenced asset image missing in public/images/: {img_file}")

    # Check for double @@ inside @php blocks in JSON-LD schema
    php_blocks = re.findall(r'@php(.*?)@endphp', content, re.DOTALL)
    for pb in php_blocks:
        if "@@type" in pb or "@@context" in pb:
            issues.append(f"[{rel_path}] Found double @@ inside @php block (will output invalid @@ JSON keys): {pb[:100]}")

print(f"\nAudit completed across {len(blade_files)} Blade files.")
print(f"Total potential issues detected: {len(issues)}")
for iss in issues[:30]:
    print(" -", iss)
