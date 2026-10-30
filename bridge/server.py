"""Durable HTTP-to-HTTPS energy bridge, standard library only."""
import base64
import json
import os
import sqlite3
import ssl
import threading
import time
import uuid
from datetime import datetime, timezone
from http.server import BaseHTTPRequestHandler, ThreadingHTTPServer
from pathlib import Path
from urllib.parse import urlsplit
from urllib.request import Request, build_opener, HTTPSHandler, HTTPRedirectHandler

DATA_DIR = Path(os.environ.get('DATA_DIR', '/data'))
MAX_BODY = 128 * 1024


def connect():
    db = sqlite3.connect(DATA_DIR / 'queue.sqlite3', timeout=15)
    db.row_factory = sqlite3.Row
    db.execute('PRAGMA synchronous=FULL')
    return db


def initialize():
    DATA_DIR.mkdir(parents=True, exist_ok=True)
    with connect() as db:
        db.execute('PRAGMA journal_mode=WAL')
        db.execute('''CREATE TABLE IF NOT EXISTS queue (
            sequence INTEGER PRIMARY KEY, event_id TEXT UNIQUE NOT NULL,
            envelope TEXT NOT NULL, attempts INTEGER NOT NULL DEFAULT 0,
            next_attempt REAL NOT NULL DEFAULT 0, last_status INTEGER,
            last_error TEXT, delivered_at TEXT)''')
        db.execute('CREATE INDEX IF NOT EXISTS due ON queue(delivered_at,next_attempt,sequence)')


class NoRedirect(HTTPRedirectHandler):
    def redirect_request(self, req, fp, code, msg, headers, newurl):
        return None


def deliver(envelope, url, token):
    request = Request(url, data=json.dumps(envelope).encode(), method='POST',
                      headers={'Content-Type': 'application/json', 'X-Meter-Token': token})
    opener = build_opener(NoRedirect(), HTTPSHandler(context=ssl.create_default_context()))
    with opener.open(request, timeout=15) as response:
        if response.status != 200:
            raise ValueError('Unexpected HTTP response')
        result = json.loads(response.read(65536))
        if result.get('ok') is not True or result.get('event_id') != envelope['event_id']:
            raise ValueError('Acknowledgement missing or mismatched')
        return response.status


def forward_once(url, token, sender=deliver):
    with connect() as db:
        row = db.execute('''SELECT * FROM queue WHERE delivered_at IS NULL
            AND next_attempt <= ? ORDER BY sequence LIMIT 1''', (time.time(),)).fetchone()
    if row is None:
        return False
    try:
        status = sender(json.loads(row['envelope']), url, token)
        with connect() as db:
            db.execute('''UPDATE queue SET delivered_at=?, last_status=?, last_error=NULL,
                attempts=attempts+1 WHERE event_id=?''',
                (datetime.now(timezone.utc).isoformat(), status, row['event_id']))
    except Exception as error:
        # Never store URLs, tokens or remote error bodies in logs/status.
        status = getattr(error, 'code', None)
        attempts = row['attempts'] + 1
        delay = min(300, 2 ** min(attempts, 8))
        with connect() as db:
            db.execute('''UPDATE queue SET attempts=?, next_attempt=?, last_status=?,
                last_error=? WHERE event_id=?''',
                (attempts, time.time() + delay, status,
                 'HTTP ' + str(status) if status else type(error).__name__, row['event_id']))
    return True


def worker(stop, url, token):
    while not stop.is_set():
        try:
            if not forward_once(url, token):
                stop.wait(1)
        except Exception:
            # A storage error must not kill the forwarding thread permanently.
            stop.wait(5)


class Handler(BaseHTTPRequestHandler):
    def setup(self):
        super().setup()
        self.connection.settimeout(15)

    def log_message(self, format, *args):
        # Request paths may contain credentials configured on the meter.
        pass

    def reply(self, status, value):
        body = json.dumps(value, ensure_ascii=False).encode()
        self.send_response(status)
        self.send_header('Content-Type', 'application/json; charset=utf-8')
        self.send_header('Cache-Control', 'no-store')
        self.send_header('Content-Length', str(len(body)))
        self.end_headers()
        self.wfile.write(body)

    def receive(self):
        if self.headers.get('Transfer-Encoding'):
            return self.reply(400, {'error': 'Transfer-Encoding unsupported'})
        try:
            size = int(self.headers.get('Content-Length', '0'))
        except ValueError:
            return self.reply(400, {'error': 'Invalid length'})
        if size < 0 or size > MAX_BODY:
            return self.reply(413, {'error': 'Payload too large'})
        try:
            body = self.rfile.read(size)
            if len(body) != size:
                return self.reply(400, {'error': 'Incomplete body'})
            event_id = str(uuid.uuid4())
            envelope = {'protocol': 1, 'bridge_id': os.environ.get('BRIDGE_ID', 'orangepi-energy'),
                        'event_id': event_id, 'received_at': datetime.now(timezone.utc).isoformat(),
                        'source': self.client_address[0], 'method': self.command,
                        'content_type': self.headers.get('Content-Type', ''),
                        'query': urlsplit(self.path).query,
                        'body_base64': base64.b64encode(body).decode()}
            with connect() as db:
                db.execute('INSERT INTO queue(event_id,envelope) VALUES(?,?)',
                           (event_id, json.dumps(envelope)))
            # Only acknowledge after the durable database transaction commits.
            self.reply(200, {'ok': True, 'event_id': event_id, 'cached': True})
        except (sqlite3.Error, OSError):
            self.reply(503, {'error': 'Unable to persist; retry required'})

    def do_POST(self):
        if urlsplit(self.path).path.rstrip('/') == '/api/energia/medidor':
            return self.receive()
        self.reply(404, {'error': 'Not found'})

    def do_GET(self):
        path = urlsplit(self.path).path.rstrip('/') or '/'
        if path == '/api/energia/medidor':
            return self.receive()
        if path not in ('/', '/health'):
            return self.reply(404, {'error': 'Not found'})
        try:
            with connect() as db:
                counts = db.execute('''SELECT COUNT(*) total,
                    SUM(delivered_at IS NULL) pending, MAX(delivered_at) last_delivery FROM queue''').fetchone()
                error = db.execute('''SELECT last_status,last_error FROM queue
                    WHERE delivered_at IS NULL AND attempts > 0 ORDER BY sequence DESC LIMIT 1''').fetchone()
            self.reply(200, {'ok': True, 'service': 'PY9MT Energia Bridge',
                             'version': os.environ.get('BRIDGE_VERSION', 'development'),
                             'cached': counts['total'], 'pending': counts['pending'] or 0,
                             'delivered': counts['total'] - (counts['pending'] or 0),
                             'last_delivery': counts['last_delivery'],
                             'last_error': dict(error) if error else None})
        except sqlite3.Error:
            self.reply(503, {'ok': False, 'error': 'Storage unavailable'})


if __name__ == '__main__':
    url, token = os.environ['ONLINE_URL'], os.environ['ONLINE_TOKEN']
    parsed = urlsplit(url)
    if parsed.scheme != 'https' or not parsed.hostname or parsed.username or parsed.password or parsed.fragment:
        raise SystemExit('ONLINE_URL must be an HTTPS endpoint without embedded credentials')
    if not token:
        raise SystemExit('ONLINE_TOKEN is required')
    initialize()
    stop = threading.Event()
    thread = threading.Thread(target=worker, args=(stop, url, token), daemon=True)
    thread.start()
    server = ThreadingHTTPServer(('0.0.0.0', int(os.environ.get('PORT', '8080'))), Handler)
    print('PY9MT Energia Bridge listening', flush=True)
    try:
        server.serve_forever()
    finally:
        stop.set()
        server.server_close()
