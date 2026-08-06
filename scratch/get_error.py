import urllib.request
import re

url = "https://dunesdiscoverytourism.com/evening-desert-safari-dubai"
try:
    req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
    html = urllib.request.urlopen(req).read().decode('utf-8')
    print("SUCCESS 200 OK!")
except urllib.error.HTTPError as e:
    html = e.read().decode('utf-8', errors='ignore')
    print(f"HTTP ERROR {e.code}")
    # Print lines containing error or text
    for line in html.split('\n'):
        if any(w in line.lower() for w in ['undefined', 'error', 'call to', 'exception', 'syntax', 'cannot']):
            print(line[:150])
