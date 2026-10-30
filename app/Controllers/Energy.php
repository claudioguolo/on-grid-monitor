<?php
namespace App\Controllers;
use App\Libraries\Measurements;
use App\Libraries\Parameters;
use CodeIgniter\HTTP\ResponseInterface;

class Energy extends BaseController
{
    private function protectedAccess(): ?ResponseInterface
    {
        if (\App\Libraries\PanelAuth::signedIn()) return null;
        $path=trim($this->request->getUri()->getPath(),'/');
        if(str_contains('/'.$path,'/api/')){
            return $this->response->setStatusCode(401)->setHeader('Cache-Control','no-store')->setJSON(['error'=>'Sessao expirada ou autenticacao necessaria']);
        }
        return redirect()->to(\App\Libraries\PanelAuth::appPath().'/login')->setHeader('Cache-Control','no-store');
    }
    private function json($data): ResponseInterface
    {
        return $this->response->setHeader('Cache-Control','no-store')->setHeader('X-Content-Type-Options','nosniff')->setJSON($data);
    }
    public function index()
    {
        if ($denied=$this->protectedAccess()) return $denied;
        helper('form');
        return $this->response->setHeader('Cache-Control','no-store')->setBody(view('dashboard'));
    }
    public function health()
    {
        db_connect()->query('SELECT 1');
        return $this->json(['ok'=>true]);
    }
    public function settings()
    {
        if ($denied=$this->protectedAccess()) return $denied;
        return $this->json(Parameters::load()+['version'=>trim(file_get_contents(ROOTPATH.'VERSION'))]);
    }
    public function receive()
    {
        $config=Parameters::load(); $token=(string)env('METER_TOKEN',env('meter.token',''));
        if ($token==='' && ENVIRONMENT==='production') return $this->response->setStatusCode(503)->setJSON(['error'=>'Configure meter.token']);
        $provided=(string)($this->request->getGet('token') ?? $this->request->getHeaderLine('X-Meter-Token'));
        if ($token!=='' && !hash_equals($token,$provided)) return $this->response->setStatusCode(401)->setJSON(['error'=>'Token invalido']);
        $body=(string)$this->request->getBody();
        if (strlen($body)>$config['max_payload_bytes']) return $this->response->setStatusCode(413)->setJSON(['error'=>'Payload muito grande']);
        $contentType=$this->request->getHeaderLine('Content-Type'); $query=$this->request->getGet(); unset($query['token']);
        $payload=json_decode($body,true);
        if (json_last_error()!==JSON_ERROR_NONE) {
            if (str_contains($contentType,'application/x-www-form-urlencoded')) parse_str($body,$payload);
            else $payload=$body===''?new \stdClass():['raw'=>mb_convert_encoding($body,'UTF-8','UTF-8')];
        }
        $parsed=['query'=>$query?:new \stdClass(),'payload'=>$payload];
        $db=db_connect();
        $db->table('readings')->insert(['received_at'=>(new \DateTimeImmutable('now',new \DateTimeZone('UTC')))->format('Y-m-d\TH:i:s.uP'),
            'source'=>$this->request->getIPAddress(),'method'=>strtoupper($this->request->getMethod()),
            'content_type'=>$contentType,'query'=>http_build_query($query),'body'=>$body,
            'parsed'=>json_encode($parsed,JSON_THROW_ON_ERROR|JSON_INVALID_UTF8_SUBSTITUTE)]);
        return $this->json(['ok'=>true,'id'=>(int)$db->insertID()]);
    }
    public function receiveArchive()
    {
        $token=(string)env('METER_TOKEN',env('meter.token',''));
        if($token==='')return $this->response->setStatusCode(503)->setJSON(['error'=>'Configure meter.token']);
        if(!hash_equals($token,$this->request->getHeaderLine('X-Meter-Token')))return $this->response->setStatusCode(401)->setJSON(['error'=>'Token invalido']);
        $body=(string)$this->request->getBody();
        if(strlen($body)>1048576)return $this->response->setStatusCode(413)->setJSON(['error'=>'Lote muito grande']);
        try{
            $input=json_decode($body,true,512,JSON_THROW_ON_ERROR);
            if(!is_array($input)||!is_string($input['dataset']??null)||!is_array($input['records']??null)||!array_is_list($input['records'])||count($input['records'])<1||count($input['records'])>100)throw new \InvalidArgumentException();
            $rows=[];
            foreach($input['records'] as $record){if(!is_array($record))throw new \InvalidArgumentException();$rows[]=\App\Libraries\ArchivedReading::row($record,$input['dataset']);}
        }catch(\JsonException|\InvalidArgumentException $e){return $this->response->setStatusCode(400)->setJSON(['error'=>'Lote invalido']);}
        $db=db_connect();$inserted=0;$duplicates=0;$ack=[];
        $db->transException(true)->transBegin();
        try{
            foreach($rows as $i=>$row){
                $existing=$db->table('readings')->where('import_key',$row['import_key'])->get()->getRowArray();
                $digest=\App\Libraries\ArchivedReading::digest($row);
                if($existing){
                    if(!hash_equals($digest,\App\Libraries\ArchivedReading::digest($existing)))throw new \DomainException('Conflito de historico');
                    $duplicates++;
                }else{
                    if(!$db->table('readings')->insert($row))throw new \RuntimeException('Falha ao salvar historico');
                    $inserted++;
                }
                $ack[]=['source_id'=>$input['records'][$i]['source_id'],'digest'=>$digest];
            }
            if(!$db->transStatus())throw new \RuntimeException('Falha na transacao');
            $db->transCommit();
        }catch(\Throwable $e){$db->transRollback();if($e instanceof \DomainException)return $this->response->setStatusCode(409)->setJSON(['error'=>'Registro existente com conteudo diferente']);throw $e;}
        return $this->json(['ok'=>true,'inserted'=>$inserted,'duplicates'=>$duplicates,'records'=>$ack]);
    }
    public function receiveBridge()
    {
        $token=(string)env('METER_TOKEN',env('meter.token',''));
        if ($token==='') return $this->response->setStatusCode(503)->setJSON(['error'=>'Configure meter.token']);
        if (!hash_equals($token,$this->request->getHeaderLine('X-Meter-Token'))) {
            return $this->response->setStatusCode(401)->setJSON(['error'=>'Token invalido']);
        }
        $config=Parameters::load();$body=(string)$this->request->getBody();
        if (strlen($body)>$config['max_payload_bytes']*2+8192) return $this->response->setStatusCode(413)->setJSON(['error'=>'Envelope muito grande']);
        try {
            $event=json_decode($body,true,512,JSON_THROW_ON_ERROR);
            if (!is_array($event)) throw new \InvalidArgumentException('Envelope invalido');
            $row=\App\Libraries\BridgeEnvelope::row($event,$config['max_payload_bytes']);
        } catch (\JsonException|\InvalidArgumentException $e) {
            return $this->response->setStatusCode(400)->setJSON(['error'=>'Envelope invalido ou relogio incorreto']);
        }
        $db=db_connect();
        $existing=$db->table('readings')->select('id,parsed')->where('import_key',$row['import_key'])->get()->getRowArray();
        if (!$existing) {
            try {
                if (!$db->table('readings')->insert($row)) throw new \RuntimeException('Falha ao armazenar evento');
                return $this->json(['ok'=>true,'event_id'=>$event['event_id'],'id'=>(int)$db->insertID(),'duplicate'=>false]);
            } catch (\Throwable $error) {
                // A retry may race a prior request whose acknowledgement was lost.
                $existing=$db->table('readings')->select('id,parsed')->where('import_key',$row['import_key'])->get()->getRowArray();
                if (!$existing) throw $error;
            }
        }
        $stored=json_decode($existing['parsed'],true)['bridge']['digest']??'';
        $incoming=json_decode($row['parsed'],true)['bridge']['digest'];
        if (!hash_equals($stored,$incoming)) return $this->response->setStatusCode(409)->setJSON(['error'=>'Evento existente com conteudo diferente']);
        return $this->json(['ok'=>true,'event_id'=>$event['event_id'],'id'=>(int)$existing['id'],'duplicate'=>true]);
    }
    public function readings()
    {
        if ($denied=$this->protectedAccess()) return $denied;
        $db=db_connect(); $total=$db->table('readings')->countAllResults();
        $rows=$db->table('readings')->select('id,received_at,source,method,content_type,query,body,parsed')->orderBy('received_at','DESC')->orderBy('id','DESC')->limit(100)->get()->getResultArray();
        foreach ($rows as &$row) { $row['id']=(int)$row['id']; $row['parsed']=json_decode($row['parsed']); $row['body']=mb_convert_encoding($row['body'],'UTF-8','UTF-8'); }
        return $this->json(['total'=>$total,'readings'=>$rows]);
    }
    private function history(bool $technical)
    {
        if ($denied=$this->protectedAccess()) return $denied;
        $c=Parameters::load(); $hours=(string)($this->request->getGet('hours') ?? $c['default_chart_hours']);
        if (!in_array($hours,['1','6','24','168'],true)) return $this->response->setStatusCode(400)->setJSON(['error'=>'Periodo invalido']);
        $since=(new \DateTimeImmutable('now',new \DateTimeZone('UTC')))->modify('-'.$hours.' hours')->format('Y-m-d\TH:i:s.uP');
        $rows=db_connect()->table('readings')->select('received_at,parsed')->where('received_at >=',$since)->orderBy('received_at','DESC')->orderBy('id','DESC')->limit($c['history_limit']+1)->get()->getResultArray();
        $truncated=count($rows)>$c['history_limit']; $points=[];
        foreach (array_reverse(array_slice($rows,0,$c['history_limit'])) as $row) { $point=Measurements::point($row,$technical); if ($point!==null) $points[]=$point; }
        return $this->json(['points'=>$points,'truncated'=>$truncated]);
    }
    public function power() { return $this->history(false); }
    public function technical() { return $this->history(true); }
    public function summary()
    {
        if ($denied=$this->protectedAccess()) return $denied;
        $c=Parameters::load(); $zone=new \DateTimeZone($c['timezone']);
        $now=new \DateTimeImmutable('now',$zone);
        $since=$now->modify('first day of this month')->setTime(0,0)->modify('-1 month')->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d\TH:i:s.uP');
        $result=db_connect()->table('readings')->select('received_at,parsed')->where('received_at >=',$since)->orderBy('received_at')->orderBy('id')->get();
        $rows=(function()use($result){while($row=$result->getUnbufferedRow('array'))yield $row;})();
        $summary=Measurements::summary($rows,$c,$now); $result->freeResult();
        return $this->json(['timezone'=>$c['timezone'],'energy'=>$summary]);
    }
    public function historyForm()
    {
        if($denied=$this->protectedAccess())return $denied;
        helper('form');
        return view('history',['message'=>'']);
    }
    public function importHistory()
    {
        if($denied=$this->protectedAccess())return $denied;
        helper('form');
        try{
            $file=$this->request->getFile('history');
            if(!$file || !$file->isValid() || $file->getSize()>4*1024*1024)throw new \InvalidArgumentException('Arquivo invalido ou maior que 4 MiB');
            $rows=\App\Libraries\History::parse(file_get_contents($file->getTempName()),$file->getClientName());
            $result=\App\Libraries\History::merge($rows);
            $message=$result['inserted'].' registros importados; '.$result['skipped'].' ja existentes.';
        }catch(\InvalidArgumentException $e){$this->response->setStatusCode(400);$message=$e->getMessage();}
        return view('history',compact('message'));
    }

}
