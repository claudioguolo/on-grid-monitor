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
