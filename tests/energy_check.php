<?php
// Domain checks independent of database and PHPUnit installation.
define('ROOTPATH', dirname(__DIR__).DIRECTORY_SEPARATOR);
require ROOTPATH.'vendor/autoload.php';
use App\Libraries\Measurements as M;
use App\Libraries\History;
use App\Libraries\Parameters;
function check($value,$message){if(!$value)throw new RuntimeException($message);}
$row=['received_at'=>'2026-10-28T15:00:00+00:00','parsed'=>json_encode(['payload'=>['pa'=>'5.40','pb'=>'-268.92','pc'=>'999','freq'=>'nan']])];
check(abs(M::point($row,false)['injection']-263.52)<0.00001,'Saldo bifasico');
check(M::point($row,true)['freq']===null,'Numero nao finito');
check(!array_key_exists('pc',M::point($row,true)),'Fase C excluida');
$text='12:50:44:-18544:-41415:-1:-59961:1062:699:0:1790:104:281:0:381:330:335:0:12902:12475:3';
$records=History::parse($text,'28102026.txt');$p=json_decode($records[0]['parsed'],true)['payload'];
check($p['pa']==='-185.44' && $p['uarms']==='129.02','Escala TXT');
check($records[0]['received_at']==='2026-10-28T16:50:44+00:00','Fuso TXT');
try{History::parse('29:00:00'.substr($text,8),'28102026.txt');throw new RuntimeException('Horario deveria falhar');}catch(InvalidArgumentException $e){}
$rows=[];
foreach([['2026-10-28T03:50:00+00:00',[9,9,4,4]],['2026-10-28T04:00:00+00:00',[10,10,5,5]],['2026-10-28T05:00:00+00:00',[11,12,6,7]],['2026-10-28T06:00:00+00:00',[0,0,0,0]],['2026-10-28T07:00:00+00:00',[1,1,2,2]]] as [$stamp,$v])$rows[]=['received_at'=>$stamp,'parsed'=>json_encode(['payload'=>array_combine(['epa_c','epb_c','epa_g','epb_g'],$v)])];
$s=M::summary($rows,Parameters::load(),new DateTimeImmutable('2026-10-28T07:00:00+00:00'));
check($s['today']['consumption']===5.0 && $s['today']['injection']===7.0,'Energia por periodo');
check($s['today']['resets']===1 && $s['previous_month']['consumption']===null,'Reinicio e historico parcial');
echo "Calculos, TXT, fuso, campos invalidos e cobertura: OK\n";
$dailyRows=[];
foreach([
 ['2026-10-29T03:50:00+00:00',[9,9,4,4]],
 ['2026-10-29T04:00:00+00:00',[10,10,5,5]],
 ['2026-10-29T16:00:00+00:00',[11,12,7,8]],
 ['2026-10-30T04:00:00+00:00',[12,13,9,10]],
 ['2026-10-30T16:00:00+00:00',[13,14,11,12]],
 ['2026-10-31T04:00:00+00:00',[14,15,13,14]],
 ['2026-10-31T05:00:00+00:00',[0,0,0,0]],
 ['2026-10-31T06:00:00+00:00',[1,2,3,4]],
] as [$stamp,$values])$dailyRows[]=['received_at'=>$stamp,'parsed'=>json_encode(['payload'=>array_combine(['epa_c','epb_c','epa_g','epb_g'],$values)])];
$daily=M::daily($dailyRows,Parameters::load(),new DateTimeImmutable('2026-10-31T16:00:00+00:00'));
check(count($daily)===7&&$daily[0]['date']==='2026-10-25'&&$daily[6]['date']==='2026-10-31','Sete dias incluindo hoje');
check($daily[0]['consumption']===null&&$daily[0]['injection']===null&&$daily[0]['partial'],'Dia sem historico nao vira zero');
check($daily[3]['date']==='2026-10-28'&&$daily[3]['consumption']===2.0&&$daily[3]['partial'],'Fuso e historico parcial');
check($daily[4]['consumption']===5.0&&$daily[4]['injection']===9.0&&$daily[4]['balance']===4.0&&!$daily[4]['partial'],'Dia encerrado e saldo');
check($daily[5]['consumption']===4.0&&$daily[5]['injection']===8.0,'Meia-noite nao duplica energia');
check($daily[6]['consumption']===3.0&&$daily[6]['injection']===7.0&&$daily[6]['resets']===1&&$daily[6]['in_progress'],'Hoje em andamento e reinicio de contador');
$monthChange=M::daily([],Parameters::load(),new DateTimeImmutable('2026-11-03T16:00:00+00:00'));
check($monthChange[0]['date']==='2026-10-28'&&$monthChange[6]['date']==='2026-11-03','Semana atravessa mes');
echo "Comparativo diario: sete dias, fuso, fronteira meia-noite, reinicios, saldo e dados ausentes: OK\n";
