<?php
namespace App\Libraries;
final class Parameters
{
    public static function load(): array
    {
        $c = json_decode(file_get_contents(ROOTPATH.'config/software.json'), true, 512, JSON_THROW_ON_ERROR);
        $expected = ['timezone','stale_seconds','max_payload_bytes','history_limit','default_chart_hours','refresh_seconds'];
        $keys = array_keys($c); sort($keys); sort($expected);
        if ($keys !== $expected) throw new \RuntimeException('Parametros ausentes ou desconhecidos');
        new \DateTimeZone($c['timezone']);
        foreach (['stale_seconds'=>[30,86400],'max_payload_bytes'=>[1024,1048576],'history_limit'=>[100,100000],'refresh_seconds'=>[5,300]] as $key=>$range) {
            if (!is_int($c[$key]) || $c[$key]<$range[0] || $c[$key]>$range[1]) throw new \RuntimeException('Parametro invalido: '.$key);
        }
        if (!in_array($c['default_chart_hours'],[1,6,24,168],true)) throw new \RuntimeException('Periodo invalido');
        return $c;
    }
}
