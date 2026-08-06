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
    
    idx = html.find('const markdown = ')
    if idx != -1:
        end_idx = html.find(';\n', idx)
        raw = html[idx+17:end_idx]
        # Unescape JS string
        clean = raw.encode('utf-8').decode('unicode_escape')
        print("=== IGNITION MARKDOWN ERROR TRACE ===")
        print(clean[:3000])
