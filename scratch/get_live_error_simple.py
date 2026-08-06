import urllib.request
import re

url = "https://dunesdiscoverytourism.com/"
try:
    req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
    html = urllib.request.urlopen(req).read().decode('utf-8', errors='ignore')
    print("SUCCESS 200 OK!")
except urllib.error.HTTPError as e:
    html = e.read().decode('utf-8', errors='ignore')
    
    idx = html.find('const markdown = ')
    if idx != -1:
        end = html.find(';\n', idx)
        raw = html[idx+17:end]
        clean = raw.encode('utf-8').decode('unicode_escape')
        print("=== LIVE IGNITION ERROR ===")
        print(clean[:1500])
    else:
        print("Markdown variable not found in response")
