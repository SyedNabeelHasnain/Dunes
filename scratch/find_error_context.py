import urllib.request
import re

url = "https://dunesdiscoverytourism.com/"
try:
    req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
    html = urllib.request.urlopen(req).read().decode('utf-8', errors='ignore')
    print("SUCCESS 200 OK!")
except urllib.error.HTTPError as e:
    html = e.read().decode('utf-8', errors='ignore')
    
    # Print all occurrences of 'syntax error' or 'unexpected' or 'index.blade.php'
    for match in re.finditer(r'(syntax error[^\n]*|unexpected[^\n]*|index\.blade\.php[^\n]*)', html, re.IGNORECASE):
        print("MATCH:", match.group(0)[:150])
