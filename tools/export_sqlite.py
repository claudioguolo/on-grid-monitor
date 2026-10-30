"""Export a SQLite snapshot to lossless NDJSON for php spark energia:import-python."""
import argparse
import base64
import json
import sqlite3
parser = argparse.ArgumentParser()
parser.add_argument('sqlite')
parser.add_argument('output')
args = parser.parse_args()
with sqlite3.connect('file:' + args.sqlite + '?mode=ro&immutable=1', uri=True) as db:
    db.row_factory = sqlite3.Row
    with open(args.output, 'w', encoding='utf-8') as output:
        for row in db.execute('SELECT * FROM readings ORDER BY received_at, id'):
            data = dict(row)
            data['body_base64'] = base64.b64encode(bytes(data.pop('body'))).decode()
            output.write(json.dumps(data, ensure_ascii=False) + '\n')
