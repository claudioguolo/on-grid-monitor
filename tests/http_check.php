<?php
// Run only against an isolated database/container with test-only credentials.
function check($value,$message){if(!$value)throw new RuntimeException($message);}
function request($path,$method='GET',$body='',$headers=[]){
    $context=stream_context_create(['http'=>['method'=>$method,'content'=>$body,'header'=>implode("\r\n",$headers),'ignore_errors'=>true,'follow_location'=>0,'timeout'=>15]]);
    $text=file_get_contents('http://localhost'.$path,false,$context);$h=$http_response_header??[];
    preg_match('/\s(\d{3})\s/',$h[0]??'',$m);
    return [(int)($m[1]??0),$text,$h];
}
$auth='Authorization: Basic '.base64_encode('test:test-only-password');
check(request('/api/readings')[0]===401,'Painel deve exigir autenticacao');
check(request('/api/settings','GET','',[$auth])[0]===200,'Credenciais do painel');
check(request('/api/energia/medidor','POST','{}',['Content-Type: application/json'])[0]===401,'Medidor deve exigir token');
$path='/api/energia/medidor?token=test-only-token';
$body='{"id":"1","pa":"100","pb":"-300","qa":"-20","freq":"59.97"}';
check(request($path,'POST',$body,['Content-Type: application/json'])[0]===200,'Recepcao JSON');
check(request($path,'POST','id=1&pa=100&pb=-300',['Content-Type: application/x-www-form-urlencoded'])[0]===200,'Recepcao formulario');
check(request($path.'&id=1&pa=100&pb=-300')[0]===200,'Recepcao GET');
check(request($path,'POST','01:02:03:400',['Content-Type: text/plain'])[0]===200,'Recepcao texto');
[$status,$text]=request('/api/readings','GET','',[$auth]);$data=json_decode($text,true,512,JSON_THROW_ON_ERROR);
check($status===200&&$data['total']===4,'Quatro envios armazenados');
check($data['readings'][3]['body']===$body,'Corpo original preservado');
check(!str_contains($data['readings'][3]['query'],'token'),'Token nao armazenado');
[$status,$text]=request('/api/power?hours=24','GET','',[$auth]);$d=json_decode($text,true);
check($status===200&&$d['points'][0]['injection']==200,'Grafico bifasico');
check(request('/api/technical?hours=abc','GET','',[$auth])[0]===400,'Periodo invalido');
check(request('/api/summary','GET','',[$auth])[0]===200,'Resumo');
check(request('/health')[0]===200,'Saude do MySQL');
[$status,$page,$headers]=request('/history','GET','',[$auth]);
check($status===200,'Formulario de historico');
preg_match('/name="([^\"]+)" value="([^\"]+)"/',$page,$csrf);
check(count($csrf)===3,'Token CSRF no formulario');
$cookies=[];foreach($headers as $header)if(stripos($header,'Set-Cookie:')===0)$cookies[]=explode(';',trim(substr($header,11)))[0];
$boundary='energiaValidationBoundary';$line='12:50:44:-18544:-41415:-1:-59961:1062:699:0:1790:104:281:0:381:330:335:0:12902:12475:3';
$payload='--'.$boundary."\r\nContent-Disposition: form-data; name=\"".$csrf[1]."\"\r\n\r\n".$csrf[2]."\r\n--".$boundary."\r\nContent-Disposition: form-data; name=\"history\"; filename=\"28102026.txt\"\r\nContent-Type: text/plain\r\n\r\n".$line."\r\n--".$boundary."--\r\n";
[$status,$result]=request('/history','POST',$payload,[$auth,'Cookie: '.implode('; ',$cookies),'Content-Type: multipart/form-data; boundary='.$boundary]);
check($status===200&&str_contains($result,'1 registros importados'),'Upload TXT e CSRF');
[$status,$text]=request('/api/readings','GET','',[$auth]);$d=json_decode($text,true);check($d['total']===5,'Historico acrescentado');
check($d['readings'][0]['method']!=='IMPORT','Historico nao deve substituir ultima telemetria');
echo "HTTP: autenticacao, token, POST/GET, dados brutos, graficos, periodos, resumo e upload CSRF: OK\n";
$event=['protocol'=>1,'bridge_id'=>'test-orange','event_id'=>'test-event-1','received_at'=>'2026-10-28T16:00:00.123456+00:00',
    'source'=>'192.168.0.10','method'=>'POST','content_type'=>'application/json','query'=>'',
    'body_base64'=>base64_encode('{"id":"1","pa":"100","pb":"-300"}')];
$headers=['Content-Type: application/json','X-Meter-Token: test-only-token'];
[$status,$text]=request('/api/energia/bridge','POST',json_encode($event),$headers);$first=json_decode($text,true);
check($status===200&&$first['ok']&&$first['event_id']===$event['event_id']&&!$first['duplicate'],'Ponte: primeiro envio');
[$status,$text]=request('/api/energia/bridge','POST',json_encode($event),$headers);$second=json_decode($text,true);
check($status===200&&$second['duplicate']&&$second['id']===$first['id'],'Ponte: reenvio idempotente');
[$status,$text]=request('/api/readings','GET','',[$auth]);$rows=json_decode($text,true)['readings'];
check(count($rows)===6,'Ponte: apenas um novo registro');
$stored=array_values(array_filter($rows,fn($row)=>$row['id']===$first['id']))[0];
check($stored['received_at']===$event['received_at'],'Ponte: horario original');
check($stored['body']===base64_decode($event['body_base64']),'Ponte: corpo original');
$event['body_base64']=base64_encode('{"id":"alterado"}');
check(request('/api/energia/bridge','POST',json_encode($event),$headers)[0]===409,'Ponte: conflito de identidade');
$event['received_at']='invalid';
check(request('/api/energia/bridge','POST',json_encode($event),$headers)[0]===400,'Ponte: horario invalido');
check(request('/api/energia/bridge','POST','{}',['Content-Type: application/json'])[0]===401,'Ponte: exige token');
echo "Ponte HTTPS: autenticacao, horario original, idempotencia e conflitos: OK\n";
