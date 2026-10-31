<?php
namespace App\Libraries;
final class Measurements
{
    public static function technicalKeys(array $phases): array
    {
        $keys=['freq'];
        foreach($phases as $phase)foreach(['p%s','q%s','s%s','u%srms','i%srms','pf%s'] as $format)$keys[]=sprintf($format,$phase);
        return $keys;
    }
    private static function counters(array $payload,array $phases): ?array
    {
        $values=[];
        foreach(['c','g'] as $kind)foreach($phases as $phase){
            $value=self::number($payload['ep'.$phase.'_'.$kind]??null);
            if($value===null||$value<0)return null;
            $values[]=$value;
        }
        return $values;
    }
    public static function number($value): ?float
    {
        if (!is_numeric($value)) return null;
        $number = (float)$value;
        return is_finite($number) ? $number : null;
    }
    public static function point(array $row, bool $technical, array $phases=['a','b']): ?array
    {
        $data = json_decode($row['parsed'],true);
        $p = $data['payload'] ?? null;
        if (!is_array($p)) return null;
        $out = ['time'=>$row['received_at']];
        if ($technical) {
            $valid = false;
            foreach (self::technicalKeys($phases) as $key) { $out[$key] = self::number($p[$key] ?? null); $valid = $valid || $out[$key] !== null; }
            return $valid ? $out : null;
        }
        $values=[];
        foreach($phases as $phase){$values[$phase]=self::number($p['p'.$phase]??null);if($values[$phase]===null)return null;}
        $net=array_sum($values);
        return $out+$values+['net'=>$net,'consumption'=>max($net,0),'injection'=>max(-$net,0)];
    }

    public static function summary(iterable $rows, array $config, ?\DateTimeImmutable $now=null): array
    {
        $phases=$config['active_phases']??['a','b'];
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
            $values=self::counters($p,$phases);if($values===null)continue;
            $stamp=new \DateTimeImmutable($row['received_at']);
            foreach ($periods as $name=>[$start,$end]) {
                if ($stamp<$start || $stamp>$end || ($name==='previous_month' && $stamp==$end)) continue;
                $s=&$states[$name];
                if ($s['last']!==null) {
                    $delta=array_map(fn($a,$b)=>$a-$b,$values,$s['last']);
                    if (min($delta)<-0.000001) $s['resets']++;
                    else { $s['consumption']+=array_sum(array_slice($delta,0,count($phases))); $s['injection']+=array_sum(array_slice($delta,count($phases))); }
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
        $phases=$config['active_phases']??['a','b'];
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
            $values=self::counters($payload,$phases);if($values===null)continue;
            $stamp=new \DateTimeImmutable($row['received_at']);
            foreach($periods as $key=>[$start,$end]){
                // A midnight sample is the closing boundary of one day and baseline of the next.
                if($stamp<$start||$stamp>$end)continue;
                $state=&$states[$key];
                if($state['last']!==null){
                    $delta=array_map(fn($after,$before)=>$after-$before,$values,$state['last']);
                    if(min($delta)<-0.000001)$state['resets']++;
                    else{$state['consumption']+=array_sum(array_slice($delta,0,count($phases)));$state['injection']+=array_sum(array_slice($delta,count($phases)));}
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
