import os
import re

file_path = os.path.abspath(os.path.join(os.path.dirname(__file__), '../resources/views/tours/show.blade.php'))

with open(file_path, 'r', encoding='utf-8') as f:
    lines = f.readlines()

def check_balance(pattern_start, pattern_end, name):
    count = 0
    for idx, line in enumerate(lines, 1):
        s = len(re.findall(pattern_start, line))
        e = len(re.findall(pattern_end, line))
        count += (s - e)
        if s > 0 or e > 0:
            print(f"[{name}] Line {idx:3d} [diff {s-e:+d}] [balance {count:2d}]: {line.strip()[:80]}")
    print(f"Final {name} balance: {count}\n")

check_balance(r'@section\b', r'@endsection\b', 'SECTION')
check_balance(r'@push\b', r'@endpush\b', 'PUSH')
check_balance(r'@foreach\b', r'@endforeach\b', 'FOREACH')
