<?php
// Run only against an isolated database/container with test-only credentials.
function check($value,$message){if(!$value)throw new RuntimeException($message);}
$cookieJar=[];
function request($path,$method='GET',$body='',$headers=[]){
    global $cookieJar;
    if($cookieJar)$headers[]='Cookie: '.implode('; ',array_map(fn($k,$v)=>$k.'='.$v,array_keys($cookieJar),$cookieJar));
    $context=stream_context_create(['http'=>['method'=>$method,'content'=>$body,'header'=>implode("\r\n",$headers),'ignore_errors'=>true,'follow_location'=>0,'timeout'=>15]]);
    $text=file_get_contents('http://localhost'.$path,false,$context);$h=$http_response_header??[];
    foreach($h as $header)if(stripos($header,'Set-Cookie:')===0){$pair=explode('=',explode(';',trim(substr($header,11)))[0],2);if(count($pair)===2)$cookieJar[$pair[0]]=$pair[1];}
    preg_match('/\s(\d{3})\s/',$h[0]??'',$m);
    return [(int)($m[1]??0),$text,$h];
}
$auth='';
check(request('/api/readings')[0]===401,'Painel deve exigir autenticacao');
check(request('/api/daily')[0]===401,'Comparativo diario exige autenticacao');
check(request('/')[0]===302,'Painel redireciona ao login sem janela Basic');
[$status,$page]=request('/login');check($status===200&&str_contains($page,'PY9MT'),'Tela de login');
preg_match('/name="([^\"]+)" value="([^\"]+)"/',$page,$loginCsrf);
check(count($loginCsrf)===3,'CSRF do login');
$loginBody=http_build_query([$loginCsrf[1]=>$loginCsrf[2],'username'=>'test','password'=>'wrong']);
check(request('/login','POST',$loginBody,['Content-Type: application/x-www-form-urlencoded'])[0]===401,'Senha incorreta');
[$status,$page]=request('/login');preg_match('/name="([^\"]+)" value="([^\"]+)"/',$page,$loginCsrf);
$loginBody=http_build_query([$loginCsrf[1]=>$loginCsrf[2],'username'=>'test','password'=>'test-only-password']);
[$loginStatus,$loginResult]=request('/login','POST',$loginBody,['Content-Type: application/x-www-form-urlencoded']);
check(in_array($loginStatus,[302,303],true),'Login por sessao: HTTP '.$loginStatus);
check(request('/api/settings')[0]===200,'API autenticada por sessao');
[$dailyStatus,$dailyBody]=request('/api/daily');$dailyData=json_decode($dailyBody,true);check($dailyStatus===200&&count($dailyData['days'])===7,'Comparativo diario autenticado com sete datas');
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
[$status,$result]=request('/history','POST',$payload,[$auth,'Content-Type: multipart/form-data; boundary='.$boundary]);
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
$archived=['source_id'=>'42','received_at'=>'2026-10-27T18:15:34+00:00','source'=>'history:1','method'=>'IMPORT','content_type'=>'text/plain','query'=>'',
    'body_base64'=>base64_encode('14:15:34:-30363:-62804:-1'),
    'parsed'=>'{"query":{},"payload":{"id":"1","pa":"-303.63","pb":"-628.04"},"history":{"scale":100}}'];
$batch=['dataset'=>'test-pc-mysql','records'=>[$archived]];
[$status,$text]=request('/api/energia/archive','POST',json_encode($batch),$headers);$ack=json_decode($text,true);
check($status===200&&$ack['inserted']===1&&$ack['records'][0]['source_id']==='42','Historico: importar registro');
$repeat=json_decode(request('/api/energia/archive','POST',json_encode($batch),$headers)[1],true);
check($repeat['inserted']===0&&$repeat['duplicates']===1&&$repeat['records']===$ack['records'],'Historico: reimportacao sem duplicar');
$batch['records'][0]['body_base64']=base64_encode('alterado');
check(request('/api/energia/archive','POST',json_encode($batch),$headers)[0]===409,'Historico: conflito nao sobrescreve');
check(request('/api/energia/archive','POST','{}',['Content-Type: application/json'])[0]===401,'Historico: exige token');
$latest=json_decode(request('/api/readings','GET','',[$auth])[1],true);
$history=array_values(array_filter($latest['readings'],fn($r)=>$r['source']==='history:1'&&$r['received_at']===$archived['received_at']))[0];
check($history['body']===base64_decode($archived['body_base64']),'Historico: bytes originais preservados');
check($history['parsed']['payload']['pa']==='-303.63','Historico: nao divide valores novamente');
echo "Historico em lotes: importacao, integridade, duplicatas, conflito e autenticacao: OK\n";

check(request('/logout','POST','',['X-Requested-With: XMLHttpRequest'])[0]===403,'Logout exige CSRF');
check(request('/api/settings')[0]===200,'CSRF invalido nao encerra sessao');
[$status,$page]=request('/');preg_match('/name="([^\"]+)" value="([^\"]+)"/',$page,$logoutCsrf);
check(count($logoutCsrf)===3,'CSRF do logout');
check(in_array(request('/logout','POST',http_build_query([$logoutCsrf[1]=>$logoutCsrf[2]]),['Content-Type: application/x-www-form-urlencoded'])[0],[302,303],true),'Logout');
check(request('/api/readings')[0]===401,'Sessao encerrada apos logout');
check(request('/')[0]===302,'Painel protegido apos logout');
echo "Login: tela, CSRF, senha incorreta, sessao, APIs e logout: OK\n";

$rateLimited=false;
for($attempt=0;$attempt<12;$attempt++){
 [$status,$page]=request('/login');preg_match('/name="([^\"]+)" value="([^\"]+)"/',$page,$rateCsrf);
 [$status]=request('/login','POST',http_build_query([$rateCsrf[1]=>$rateCsrf[2],'username'=>'test','password'=>'wrong']),['Content-Type: application/x-www-form-urlencoded']);
 if($status===429){$rateLimited=true;break;}
}
check($rateLimited,'Tentativas de login limitadas');
echo "Login: limite de tentativas e protecao de logout: OK\n";
