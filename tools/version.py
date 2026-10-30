"""One deterministic application version per commit on the main history."""
import argparse
import re
import subprocess
from pathlib import Path
root = Path(__file__).resolve().parents[1]
parser = argparse.ArgumentParser()
parser.add_argument('--next', action='store_true')
parser.add_argument('--stage', action='store_true')
args = parser.parse_args()
series = (root / 'VERSION_SERIES').read_text().strip()
if not re.fullmatch(r'\d+\.\d+', series):
    raise SystemExit('VERSION_SERIES deve ser MAJOR.MINOR')
revisions = ['HEAD']
merge = subprocess.run(['git', 'rev-parse', '--verify', 'MERGE_HEAD'], cwd=root, text=True, capture_output=True)
if merge.returncode == 0:
    revisions += merge.stdout.split()
result = subprocess.run(['git', 'rev-list', '--count', *revisions], cwd=root, text=True, capture_output=True)
count = int(result.stdout.strip()) if result.returncode == 0 else 0
version = f'{series}.{count + int(args.next)}'
(root / 'VERSION').write_text(version + '\n')
if args.stage:
    subprocess.run(['git', 'add', 'VERSION'], cwd=root, check=True)
print(version)
