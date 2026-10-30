"""Send an immutable NDJSON export over authenticated HTTPS, safely re-runnable."""
import argparse,base64,hashlib,json,os,ssl,struct,time
from pathlib import Path
from urllib.request import Request,build_opener,HTTPSHandler,HTTPRedirectHandler
from urllib.parse import urlsplit
class NoRedirect(HTTPRedirectHandler):
    def redirect_request(self,*args,**kwargs):return None

def digest(record):
    h=hashlib.sha256()
    for key in ['received_at','source','method','content_type','query','parsed','body']:
        value=base64.b64decode(record['body_base64'],validate=True) if key=='body' else record[key].encode()
        h.update(struct.pack('>I',len(value))+value)
    return h.hexdigest()

def main():
    p=argparse.ArgumentParser();p.add_argument('file');p.add_argument('--url',required=True);p.add_argument('--dataset',required=True);p.add_argument('--report',required=True);args=p.parse_args()
    if urlsplit(args.url).scheme!='https':raise SystemExit('HTTPS obrigatorio')
    token=os.environ['ONLINE_TOKEN'];opener=build_opener(NoRedirect(),HTTPSHandler(context=ssl.create_default_context()))
    report={'dataset':args.dataset,'confirmed':0,'inserted':0,'duplicates':0,'source_sha256':hashlib.sha256(Path(args.file).read_bytes()).hexdigest()}
    def send(records):
        body=json.dumps({'dataset':args.dataset,'records':records},ensure_ascii=False).encode();expected=[{'source_id':r['source_id'],'digest':digest(r)} for r in records]
        for attempt in range(5):
            try:
                request=Request(args.url,data=body,headers={'Content-Type':'application/json','X-Meter-Token':token},method='POST')
                with opener.open(request,timeout=40) as response:ack=json.loads(response.read(262144))
                if ack.get('ok') is not True or ack.get('records')!=expected or ack.get('inserted',0)+ack.get('duplicates',0)!=len(records):raise ValueError('Confirmacao de integridade incorreta')
                report['confirmed']+=len(records);report['inserted']+=ack['inserted'];report['duplicates']+=ack['duplicates']
                Path(args.report).write_text(json.dumps(report,indent=2)+'\n');print(json.dumps(report),flush=True);return
            except Exception as error:
                if getattr(error,'code',0) in (400,401,403,409,413) or attempt==4:raise RuntimeError('Envio interrompido: '+type(error).__name__+' HTTP '+str(getattr(error,'code',''))) from None
                time.sleep(min(10,2**attempt))
    batch=[];size=0
    for line in Path(args.file).open(encoding='utf-8'):
        if not line.strip():continue
        record=json.loads(line)
        if batch and (len(batch)>=50 or size+len(line)>700000):send(batch);batch=[];size=0
        batch.append(record);size+=len(line)
    if batch:send(batch)
if __name__=='__main__':main()
