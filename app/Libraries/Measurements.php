<?php
namespace App\Libraries;
final class Measurements
{
    public const TECHNICAL = ['pa','pb','qa','qb','sa','sb','uarms','ubrms','iarms','ibrms','pfa','pfb','freq'];
    public static function number($value): ?float
    {
        if (!is_numeric($value)) return null;
        $number = (float)$value;
        return is_finite($number) ? $number : null;
    }
    public static function point(array $row, bool $technical): ?array
    {
        $data = json_decode($row['parsed'],true);
        $p = $data['payload'] ?? null;
        if (!is_array($p)) return null;
        $out = ['time'=>$row['received_at']];
        if ($technical) {
            $valid = false;
            foreach (self::TECHNICAL as $key) { $out[$key] = self::number($p[$key] ?? null); $valid = $valid || $out[$key] !== null; }
            return $valid ? $out : null;
        }
        $a = self::number($p['pa'] ?? null); $b = self::number($p['pb'] ?? null);
        if ($a === null || $b === null) return null;
        $net=$a+$b;
        return $out+['a'=>$a,'b'=>$b,'net'=>$net,'consumption'=>max($net,0),'injection'=>max(-$net,0)];
    }
    public static function summary(iterable $rows, array $config, ?\DateTimeImmutable $now=null): array
    {
        $zone=new \DateTimeZone($config['timezone']);
        $now=($now ?? new \DateTimeImmutable())->setTimezone($zone);
        $day=$now->setTime(0,0); $month=$day->modify('first day of this month');
        $previous=$month->modify('-1 month'); $week=$day->modify('-'.((int)$day->format('N')-1).' days');
        $periods=['today'=>[$day,$now],'week'=>[$week,$now],'month'=>[$month,$now],'previous_month'=>[$previous,$month]];
        $states=[];
        foreach ($periods as $name=>$bounds) $states[$name]=['consumption'=>0.0,'injection'=>0.0,'from'=>null,'to'=>null,'samples'=>0,'resets'=>0,'last'=>null];
        foreach ($rows as $row) {
            $p=json_decode($row['parsed'],true)['payload'] ?? null;
            if (!is_array($p)) continue;
            $values=[];
            foreach (['epa_c','epb_c','epa_g','epb_g'] as $key) { $v=self::number($p[$key] ?? null); if ($v===null || $v<0) continue 2; $values[]=$v; }
            $stamp=new \DateTimeImmutable($row['received_at']);
            foreach ($periods as $name=>[$start,$end]) {
                if ($stamp<$start || $stamp>$end || ($name==='previous_month' && $stamp==$end)) continue;
                $s=&$states[$name];
                if ($s['last']!==null) {
                    $delta=array_map(fn($a,$b)=>$a-$b,$values,$s['last']);
                    if (min($delta)<-0.000001) $s['resets']++;
                    else { $s['consumption']+=$delta[0]+$delta[1]; $s['injection']+=$delta[2]+$delta[3]; }
                }
                $s['from'] ??= $row['received_at']; $s['to']=$row['received_at']; $s['samples']++; $s['last']=$values;
                unset($s);
            }
        }
        foreach ($states as $name=>&$s) {
            unset($s['last']); $enough=$s['samples']>=2;
            $s['consumption']=$enough?round($s['consumption'],6):null;
            $s['injection']=$enough?round($s['injection'],6):null;
            $s['partial']=!$enough || $s['resets']>0 || (new \DateTimeImmutable($s['from']))->getTimestamp()-$periods[$name][0]->getTimestamp()>120 || $periods[$name][1]->getTimestamp()-(new \DateTimeImmutable($s['to']))->getTimestamp()>$config['stale_seconds'];
        }
        return $states;
    }

    public static function daily(iterable $rows, array $config, ?\DateTimeImmutable $now=null): array
    {
        $zone=new \DateTimeZone($config['timezone']);$now=($now??new \DateTimeImmutable())->setTimezone($zone);
        $today=$now->setTime(0,0);$periods=[];$states=[];
        for($offset=6;$offset>=0;$offset--){
            $start=$today->modify('-'.$offset.' days');$key=$start->format('Y-m-d');
            $periods[$key]=[$start,$offset===0?$now:$start->modify('+1 day')];
            $states[$key]=['date'=>$key,'time'=>$start->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d\TH:i:sP'),
                'consumption'=>0.0,'injection'=>0.0,'balance'=>null,'samples'=>0,'resets'=>0,'from'=>null,'to'=>null,'in_progress'=>$offset===0,'last'=>null];
        }
        foreach($rows as $row){
            $payload=json_decode($row['parsed'],true)['payload']??null;if(!is_array($payload))continue;
            $values=[];foreach(['epa_c','epb_c','epa_g','epb_g'] as $key){$value=self::number($payload[$key]??null);if($value===null||$value<0)continue 2;$values[]=$value;}
            $stamp=new \DateTimeImmutable($row['received_at']);
            foreach($periods as $key=>[$start,$end]){
                // A midnight sample is the closing boundary of one day and baseline of the next.
                if($stamp<$start||$stamp>$end)continue;
                $state=&$states[$key];
                if($state['last']!==null){
                    $delta=array_map(fn($after,$before)=>$after-$before,$values,$state['last']);
                    if(min($delta)<-0.000001)$state['resets']++;
                    else{$state['consumption']+=$delta[0]+$delta[1];$state['injection']+=$delta[2]+$delta[3];}
                }
                $state['from']??=$row['received_at'];$state['to']=$row['received_at'];$state['samples']++;$state['last']=$values;unset($state);
            }
        }
        foreach($states as $key=>&$state){
            unset($state['last']);$enough=$state['samples']>=2;
            $state['consumption']=$enough?round($state['consumption'],6):null;
            $state['injection']=$enough?round($state['injection'],6):null;
            $state['balance']=$enough?round($state['injection']-$state['consumption'],6):null;
            $state['partial']=!$enough||$state['resets']>0||
                (new \DateTimeImmutable($state['from']))->getTimestamp()-$periods[$key][0]->getTimestamp()>120||
                $periods[$key][1]->getTimestamp()-(new \DateTimeImmutable($state['to']))->getTimestamp()>$config['stale_seconds'];
        }
        return array_values($states);
    }
}
