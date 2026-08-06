import urllib.request
import re

url = "https://dunesdiscoverytourism.com/"
try:
    req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
    html = urllib.request.urlopen(req).read().decode('utf-8', errors='ignore')
    print("SUCCESS 200 OK!")
except urllib.error.HTTPError as e:
    html = e.read().decode('utf-8', errors='ignore')
    
    # Save html to scratch/live_error.html for inspection
    with open('scratch/live_error.html', 'w', encoding='utf-8') as f:
        f.write(html)
        
    print("HTML saved to scratch/live_error.html. Size:", len(html))
