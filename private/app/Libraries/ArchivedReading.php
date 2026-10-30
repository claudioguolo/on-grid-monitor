<?php
namespace App\Libraries;
final class ArchivedReading
{
    public static function row(array $record, string $dataset): array
    {
        if (!preg_match('/^[a-zA-Z0-9._-]{1,80}$/D',$dataset) || !is_string($record['source_id']??null) || !preg_match('/^\d{1,20}$/D',$record['source_id'])) throw new \InvalidArgumentException('Origem invalida');
        foreach (['received_at'=>40,'source'=>255,'method'=>16,'content_type'=>255,'query'=>8192,'parsed'=>524288,'body_base64'=>524288] as $key=>$limit) {
            if (!is_string($record[$key]??null) || strlen($record[$key])>$limit) throw new \InvalidArgumentException('Campo invalido');
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:\.\d{1,6})?\+00:00$/D',$record['received_at'])) throw new \InvalidArgumentException('Horario invalido');
        try { new \DateTimeImmutable($record['received_at']);$errors=\DateTimeImmutable::getLastErrors();if($errors&&($errors['warning_count']||$errors['error_count']))throw new \Exception(); }
        catch(\Throwable $e){throw new \InvalidArgumentException('Horario invalido');}
        if (!in_array($record['method'],['GET','POST','IMPORT'],true)) throw new \InvalidArgumentException('Metodo invalido');
        json_decode($record['parsed'],true,512,JSON_THROW_ON_ERROR);
        $body=base64_decode($record['body_base64'],true);
        if($body===false||strlen($body)>262144)throw new \InvalidArgumentException('Corpo invalido');
        $row=array_intersect_key($record,array_flip(['received_at','source','method','content_type','query','parsed']));
        $row['body']=$body;
        $row['import_key']=hash('sha256','archive|'.$dataset.'|'.$record['source_id'].'|'.$record['received_at']);
        return $row;
    }
    public static function digest(array $row): string
    {
        $hash=hash_init('sha256');
        foreach(['received_at','source','method','content_type','query','parsed','body'] as $key){$value=(string)$row[$key];hash_update($hash,pack('N',strlen($value)).$value);}
        return hash_final($hash);
    }
}
