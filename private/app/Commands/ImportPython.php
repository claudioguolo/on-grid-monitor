<?php
namespace App\Commands;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
class ImportPython extends BaseCommand
{
    protected $group='Energia';
    protected $name='energia:import-python';
    protected $description='Importa NDJSON exportado do SQLite Python, em transacao e sem duplicar.';
    protected $usage='energia:import-python arquivo.ndjson';
    public function run(array $params)
    {
        $path=$params[0] ?? '';
        if (!is_file($path)) throw new \InvalidArgumentException('Informe o arquivo NDJSON');
        $db=db_connect(); $db->transException(true)->transBegin(); $inserted=0;$skipped=0;
        try {
            $file=new \SplFileObject($path,'r');
            foreach($file as $line) {
                if (trim($line)==='') continue;
                $r=json_decode($line,true,512,JSON_THROW_ON_ERROR);
                foreach(['id','received_at','source','method','content_type','query','body_base64','parsed'] as $field) if (!array_key_exists($field,$r)) throw new \RuntimeException('Campo ausente: '.$field);
                new \DateTimeImmutable($r['received_at']);
                $parsed=json_decode($r['parsed'],true,512,JSON_THROW_ON_ERROR);
                $body=base64_decode($r['body_base64'],true);
                if ($body===false) throw new \RuntimeException('Corpo invalido');
                $key=isset($parsed['history']) ? hash('sha256',($parsed['payload']['id'] ?? '1').'|'.$r['received_at']) : hash('sha256','python|'.$r['id'].'|'.$r['received_at']);
                if ($db->table('readings')->where('import_key',$key)->countAllResults()) { $skipped++;continue; }
                $row=array_intersect_key($r,array_flip(['received_at','source','method','content_type','query','parsed']));
                $row['body']=$body;$row['import_key']=$key;
                if (!$db->table('readings')->insert($row)) throw new \RuntimeException('Falha ao inserir registro');
                $inserted++;
            }
            if (!$db->transStatus()) throw new \RuntimeException('Falha na transacao');
            $db->transCommit();
        }catch(\Throwable $e){$db->transRollback();throw $e;}
        CLI::write(json_encode(['inserted'=>$inserted,'skipped'=>$skipped]));
    }
}
