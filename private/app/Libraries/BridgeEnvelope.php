<?php
namespace App\Libraries;

final class BridgeEnvelope
{
    public static function row(array $event, int $limit): array
    {
        if (($event['protocol'] ?? null) !== 1) throw new \InvalidArgumentException('Protocolo invalido');
        foreach (['bridge_id','event_id'] as $key) {
            if (!is_string($event[$key] ?? null) || !preg_match('/^[a-zA-Z0-9._-]{1,80}$/D', $event[$key])) {
                throw new \InvalidArgumentException('Identificador invalido');
            }
        }
        $input = $event['received_at'] ?? '';
        if (!is_string($input) || !preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:\.\d{1,6})?(?:Z|[+-]\d{2}:\d{2})$/D', $input)) {
            throw new \InvalidArgumentException('Horario invalido');
        }
        try {
            $stamp = new \DateTimeImmutable($input);
            $errors = \DateTimeImmutable::getLastErrors();
            if ($errors && ($errors['warning_count'] || $errors['error_count'])) throw new \Exception();
        } catch (\Throwable $e) {
            throw new \InvalidArgumentException('Horario invalido');
        }
        if ($stamp->getTimestamp() > time() + 300) throw new \InvalidArgumentException('Relogio da ponte adiantado');
        foreach (['content_type'=>255,'source'=>100,'query'=>4096,'body_base64'=>($limit*2)] as $field=>$max) {
            if (!is_string($event[$field] ?? null) || strlen($event[$field]) > $max) {
                throw new \InvalidArgumentException('Campo invalido: '.$field);
            }
        }
        $body = base64_decode($event['body_base64'], true);
        if ($body === false || strlen($body)>$limit) throw new \InvalidArgumentException('Corpo invalido');
        if (!in_array($event['method'] ?? '', ['GET','POST'], true)) throw new \InvalidArgumentException('Metodo invalido');
        parse_str($event['query'], $query); unset($query['token']);
        $payload = json_decode($body, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            if (str_contains($event['content_type'], 'application/x-www-form-urlencoded')) parse_str($body, $payload);
            else $payload = $body === '' ? new \stdClass() : ['raw'=>mb_convert_encoding($body,'UTF-8','UTF-8')];
        }
        $stamp = $stamp->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d\TH:i:s.uP');
        $digest = hash('sha256', json_encode([$stamp,$event['method'],$event['source'],$event['content_type'],$query,base64_encode($body)], JSON_THROW_ON_ERROR));
        $parsed = ['query'=>$query?:new \stdClass(),'payload'=>$payload,'bridge'=>[
            'id'=>$event['bridge_id'],'event_id'=>$event['event_id'],'digest'=>$digest,
            'arrived_at'=>(new \DateTimeImmutable('now',new \DateTimeZone('UTC')))->format('Y-m-d\TH:i:s.uP')]];
        return ['received_at'=>$stamp,'source'=>'bridge:'.$event['bridge_id'].'/'.$event['source'],
            'method'=>$event['method'],'content_type'=>$event['content_type'],'query'=>http_build_query($query),
            'body'=>$body,'parsed'=>json_encode($parsed,JSON_THROW_ON_ERROR|JSON_INVALID_UTF8_SUBSTITUTE),
            'import_key'=>hash('sha256','bridge|'.$event['bridge_id'].'|'.$event['event_id'])];
    }
}
