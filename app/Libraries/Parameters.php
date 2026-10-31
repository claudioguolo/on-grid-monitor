<?php
namespace App\Libraries;
final class Parameters
{
    public static function load(): array
    {
        $c = json_decode(file_get_contents(ROOTPATH.'config/software.json'), true, 512, JSON_THROW_ON_ERROR);
        if (function_exists('db_connect') && defined('APPPATH') && db_connect()->tableExists('system_settings')) {
            $stored=db_connect()->table('system_settings')->where('id',1)->get()->getRowArray();
            if($stored)$c=json_decode($stored['settings_json'],true,512,JSON_THROW_ON_ERROR);
        }
        return self::validate($c);
    }
    public static function validate(array $c): array
    {
        $c += ['active_phases'=>['a','b'],'power_unit'=>'W','flow_gauge_limit_w'=>1000];
        $expected = ['flow_gauge_limit_w','power_unit','active_phases','timezone','stale_seconds','max_payload_bytes','history_limit','default_chart_hours','refresh_seconds'];
        $keys = array_keys($c); sort($keys); sort($expected);
        if ($keys !== $expected) throw new \RuntimeException('Parametros ausentes ou desconhecidos');
        if (!is_array($c['active_phases']) || !array_is_list($c['active_phases']) || count($c['active_phases'])<1 || count($c['active_phases'])>3 || count(array_unique($c['active_phases'], SORT_REGULAR))!==count($c['active_phases'])) throw new \RuntimeException('Selecione pelo menos uma fase válida, sem repetições.');
        foreach ($c['active_phases'] as $phase) if (!in_array($phase,['a','b','c'],true)) throw new \RuntimeException('Fase inválida.');
        sort($c['active_phases']);
        if (!in_array($c['power_unit'],['W','kW'],true)) throw new \RuntimeException('Unidade de potência inválida.');
        new \DateTimeZone($c['timezone']);
        foreach (['flow_gauge_limit_w'=>[100,1000000],'stale_seconds'=>[30,86400],'max_payload_bytes'=>[1024,1048576],'history_limit'=>[100,100000],'refresh_seconds'=>[5,300]] as $key=>$range) {
            if (!is_int($c[$key]) || $c[$key]<$range[0] || $c[$key]>$range[1]) throw new \RuntimeException('Parametro invalido: '.$key);
        }
        if (!in_array($c['default_chart_hours'],[1,6,24,168],true)) throw new \RuntimeException('Periodo invalido');
        return $c;
    }
}
