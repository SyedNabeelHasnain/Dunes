import urllib.request
import re

url = "https://dunesdiscoverytourism.com/"
try:
    req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
    html = urllib.request.urlopen(req).read().decode('utf-8', errors='ignore')
    print("SUCCESS 200 OK!")
except urllib.error.HTTPError as e:
    html = e.read().decode('utf-8', errors='ignore')
    print(f"HTTP ERROR {e.code}")
    
    # Print lines containing ParseError or text
    lines = html.split('\n')
    for idx, l in enumerate(lines):
        if 'ParseError' in l or 'unexpected' in l or 'InvalidArgumentException' in l:
            for j in range(max(0, idx-5), min(len(lines), idx+10)):
                print(f"Line {j:4d}: {lines[j][:120]}")
            break
