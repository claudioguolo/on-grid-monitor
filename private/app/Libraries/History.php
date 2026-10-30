<?php
namespace App\Libraries;
final class History
{
    public const SHORT=['pa','pb','pc','pt','epa_c','epb_c','epc_c','ept_c','epa_g','epb_g','epc_g','ept_g','iarms','ibrms','icrms','uarms','ubrms','ucrms'];
    public const FULL=['pa','pb','pc','pt','qa','qb','qc','qt','epa_c','epb_c','epc_c','ept_c','epa_g','epb_g','epc_g','ept_g','iarms','ibrms','icrms','uarms','ubrms','ucrms','pfa','pfb','pfc','pft'];
    public static function parse(string $text,string $filename,string $device='1'): array
    {
        $stem=pathinfo(basename($filename),PATHINFO_FILENAME);
        if (!preg_match('/^\d{8}$/D',$stem)) throw new \InvalidArgumentException('Nome deve ser DDMMAAAA.txt');
        $zone=new \DateTimeZone(Parameters::load()['timezone']);
        $date=\DateTimeImmutable::createFromFormat('!dmY',$stem,$zone);
        if (!$date || $date->format('dmY')!==$stem) throw new \InvalidArgumentException('Data invalida');
        $rows=[];
        foreach(preg_split('/\R/',preg_replace('/^\xEF\xBB\xBF/','',$text)) as $i=>$line){
            if(trim($line)==='')continue;
            $v=explode(':',trim($line)); $keys=count($v)===21?self::SHORT:(count($v)===29?self::FULL:null);
            if ($keys===null) throw new \InvalidArgumentException('Quantidade de campos invalida na linha '.($i+1));
            for($j=0;$j<3;$j++)if(!ctype_digit($v[$j]))throw new \InvalidArgumentException('Horario invalido');
            if((int)$v[0]>23||(int)$v[1]>59||(int)$v[2]>59)throw new \InvalidArgumentException('Horario invalido');
            $stamp=$date->setTime((int)$v[0],(int)$v[1],(int)$v[2])->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d\TH:i:sP');
            $payload=['id'=>$device];
            foreach($keys as $j=>$key){$number=Measurements::number($v[$j+3]);if($number===null)throw new \InvalidArgumentException('Valor invalido');$payload[$key]=number_format($number/100,2,'.','');}
            $rows[]=['received_at'=>$stamp,'source'=>'history:'.$device,'method'=>'IMPORT','content_type'=>'text/plain','query'=>'','body'=>$line,
                'parsed'=>json_encode(['query'=>new \stdClass(),'payload'=>$payload,'history'=>['filename'=>basename($filename),'line'=>$i+1,'timezone'=>$zone->getName(),'scale'=>100,'timestamp'=>'device_local_time']],JSON_THROW_ON_ERROR),
                'import_key'=>hash('sha256',$device.'|'.$stamp)];
        }
        usort($rows,fn($a,$b)=>strcmp($a['received_at'],$b['received_at']));
        return $rows;
    }
    public static function merge(array $records): array
    {
        $db=db_connect();$inserted=0;$skipped=0;$db->transException(true)->transBegin();
        try{
            foreach($records as $r){
                if($db->table('readings')->where('import_key',$r['import_key'])->countAllResults()){$skipped++;continue;}
                $p=json_decode($r['parsed'],true)['payload'];$collision=false;
                foreach($db->table('readings')->select('parsed')->where('received_at',$r['received_at'])->get()->getResultArray() as $existing){
                    if((string)(json_decode($existing['parsed'],true)['payload']['id']??'')===$p['id']){$collision=true;break;}
                }
                if($collision){$skipped++;continue;}
                if(!$db->table('readings')->insert($r))throw new \RuntimeException('Falha ao importar');$inserted++;
            }
            if(!$db->transStatus())throw new \RuntimeException('Falha na transacao');$db->transCommit();
        }catch(\Throwable $e){$db->transRollback();throw $e;}
        return compact('inserted','skipped');
    }
}
