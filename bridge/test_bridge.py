import importlib.util
import json
import tempfile
import unittest
import threading
from http.server import ThreadingHTTPServer
from urllib.request import Request,urlopen
from unittest.mock import patch,MagicMock
from pathlib import Path
spec=importlib.util.spec_from_file_location('bridge',Path(__file__).with_name('server.py'))
bridge=importlib.util.module_from_spec(spec);spec.loader.exec_module(bridge)

class BridgeTests(unittest.TestCase):
    def setUp(self):
        self.tmp=tempfile.TemporaryDirectory();bridge.DATA_DIR=Path(self.tmp.name);bridge.initialize()
        self.envelope={'event_id':'event-1','body_base64':'e30='}
        with bridge.connect() as db:db.execute('INSERT INTO queue(event_id,envelope) VALUES(?,?)',('event-1',json.dumps(self.envelope)))
    def tearDown(self):self.tmp.cleanup()
    def test_failure_stays_durable_then_success_marks_delivered(self):
        def failure(*args):raise TimeoutError()
        bridge.forward_once('https://example.invalid','test',failure)
        bridge.initialize()
        with bridge.connect() as db:
            row=db.execute('SELECT * FROM queue').fetchone()
            self.assertIsNone(row['delivered_at']);self.assertEqual(row['attempts'],1)
            db.execute('UPDATE queue SET next_attempt=0')
        def success(envelope,*args):self.assertEqual(envelope,self.envelope);return 200
        bridge.forward_once('https://example.invalid','test',success)
        with bridge.connect() as db:
            row=db.execute('SELECT * FROM queue').fetchone()
            self.assertIsNotNone(row['delivered_at']);self.assertEqual(row['attempts'],2)
        self.assertFalse(bridge.forward_once('https://example.invalid','test',success))
    def test_http_receiver_stores_original_before_acknowledgement(self):
        server=ThreadingHTTPServer(('127.0.0.1',0),bridge.Handler)
        thread=threading.Thread(target=server.serve_forever,daemon=True);thread.start()
        try:
            body=b'{"id":"1","pa":"-100"}'
            request=Request('http://127.0.0.1:'+str(server.server_port)+'/api/energia/medidor',data=body,headers={'Content-Type':'application/json'})
            with urlopen(request) as response:ack=json.load(response)
            self.assertTrue(ack['cached'])
            with bridge.connect() as db:
                row=db.execute('SELECT envelope FROM queue WHERE event_id=?',(ack['event_id'],)).fetchone()
            envelope=json.loads(row['envelope'])
            self.assertEqual(bridge.base64.b64decode(envelope['body_base64']),body)
            self.assertEqual(envelope['method'],'POST')
        finally:server.shutdown();server.server_close();thread.join()
    def test_mismatched_ack_leaves_event_pending(self):
        response=MagicMock();response.__enter__.return_value=response;response.status=200
        response.read.return_value=b'{"ok":true,"event_id":"different-event"}'
        opener=MagicMock();opener.open.return_value=response
        with patch.object(bridge,'build_opener',return_value=opener):
            bridge.forward_once('https://example.invalid','test')
        with bridge.connect() as db:
            row=db.execute('SELECT * FROM queue').fetchone()
            self.assertIsNone(row['delivered_at']);self.assertEqual(row['last_error'],'ValueError')
    def test_redirects_are_not_followed(self):
        self.assertIsNone(bridge.NoRedirect().redirect_request(None,None,302,'',{},'https://else.invalid'))

if __name__=='__main__':unittest.main()
