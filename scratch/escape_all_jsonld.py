import os
import glob
import re

view_files = glob.glob('resources/views/**/*.blade.php', recursive=True)

modified_files = []
for fpath in view_files:
    with open(fpath, 'r', encoding='utf-8') as f:
        content = f.read()

    new_content = content
    # Replace unescaped "@context", "@type", "@id" inside JSON-LD scripts or template text
    # Match "@context", "@type", "@id" that are not already preceded by @ (i.e. not @@)
    new_content = re.sub(r'(?<!@)@context', '@@context', new_content)
    new_content = re.sub(r'(?<!@)@type', '@@type', new_content)
    new_content = re.sub(r'(?<!@)@id', '@@id', new_content)

    if new_content != content:
        with open(fpath, 'w', encoding='utf-8') as f:
            f.write(new_content)
        modified_files.append(fpath)

print(f"Updated {len(modified_files)} files:")
for m in modified_files:
    print(f"  - {m}")
