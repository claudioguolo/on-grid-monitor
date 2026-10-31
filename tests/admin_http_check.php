<?php
// Isolated test database only; credentials below are fictitious fixtures.
function check($value,$message){if(!$value)throw new RuntimeException($message);}
function request(&$jar,$path,$method='GET',$data=null,$extra=[]){
    $headers=$extra;if($jar)$headers[]='Cookie: '.implode('; ',array_map(fn($k,$v)=>$k.'='.$v,array_keys($jar),$jar));
    if(is_array($data)){$data=http_build_query($data);$headers[]='Content-Type: application/x-www-form-urlencoded';}
    $context=stream_context_create(['http'=>['method'=>$method,'content'=>$data??'','header'=>implode("\r\n",$headers),'ignore_errors'=>true,'follow_location'=>0,'timeout'=>15]]);
    $text=file_get_contents('http://localhost'.$path,false,$context);$h=$http_response_header??[];
    foreach($h as $header)if(stripos($header,'Set-Cookie:')===0){$pair=explode('=',explode(';',trim(substr($header,11)))[0],2);if(count($pair)===2)$jar[$pair[0]]=$pair[1];}
    preg_match('/\s(\d{3})\s/',$h[0]??'',$m);return [(int)($m[1]??0),$text];
}
function csrf($page){preg_match('/name="([^"]+)" value="([^"]+)"/',$page,$m);check(count($m)===3,'Token CSRF presente');return [$m[1]=>$m[2]];}
function login(&$jar,$name,$password){[$status,$page]=request($jar,'/login');return request($jar,'/login','POST',csrf($page)+['username'=>$name,'password'=>$password])[0];}
function postForm(&$jar,$get,$post,$data){[$status,$page]=request($jar,$get);check($status===200,'Formulario '.$get);return request($jar,$post,'POST',csrf($page)+$data)[0];}
function userId(&$jar,$username){$page=request($jar,'/admin/users')[1];preg_match_all('/<tr>(.*?)<\/tr>/s',$page,$rows);foreach($rows[1] as $row)if(str_contains($row,'>'.$username.'</td>')){preg_match('/\/admin\/users\/(\d+)/',$row,$m);return (int)$m[1];}throw new RuntimeException('Usuario nao listado');}
$admin=[];$viewer=[];$anonymous=[];
check(request($anonymous,'/admin')[0]===302,'Administracao exige login');
check(in_array(login($admin,'test','test-only-password'),[302,303]),'Administrador inicial preservado');
check(request($admin,'/admin/users')[0]===200,'Administrador acessa usuarios');
$initial=userId($admin,'test');
$person=['username'=>'viewer01','display_name'=>'Leitor de teste','role'=>'viewer','active'=>'1','password'=>'viewer-password-2026'];
check(in_array(postForm($admin,'/admin/users/new','/admin/users',$person),[302,303]),'Cadastrar consulta');
$id=userId($admin,'viewer01');
check(postForm($admin,'/admin/users/new','/admin/users',$person)===422,'Usuario duplicado rejeitado');
$weak=$person;$weak['username']='weak-user';$weak['password']='123';check(postForm($admin,'/admin/users/new','/admin/users',$weak)===422,'Senha fraca rejeitada');
check(in_array(login($viewer,'viewer01',$person['password']),[302,303]),'Consulta consegue entrar');
foreach(['/admin','/admin/users','/admin/settings','/admin/audit','/history'] as $path)check(request($viewer,$path)[0]===403,'Consulta nao acessa '.$path);
check(request($viewer,'/api/readings')[0]===200,'Consulta le medicoes');
check(request($viewer,'/api/daily')[0]===200,'Consulta acessa comparativo diario');
$page=request($viewer,'/')[1];check(!str_contains($page,'>Administração</a>'),'Menu administrativo oculto');
check(request($viewer,'/admin/users','POST',csrf($page)+$person)[0]===403,'Consulta nao grava via POST direto');
$disabled=$person;$disabled['active']='';$disabled['password']='';
check(in_array(postForm($admin,'/admin/users/'.$id,'/admin/users/'.$id,$disabled),[302,303]),'Desativar usuario');
check(request($viewer,'/api/readings')[0]===401,'Desativacao invalida sessao imediatamente');
check(login($viewer,'viewer01',$person['password'])===401,'Usuario inativo nao entra');
$person['password']='new-viewer-password-2026';
check(in_array(postForm($admin,'/admin/users/'.$id,'/admin/users/'.$id,$person),[302,303]),'Reativar e redefinir senha');
check(login($viewer,'viewer01','viewer-password-2026')===401,'Senha antiga rejeitada');
check(in_array(login($viewer,'viewer01',$person['password']),[302,303]),'Senha nova aceita');
$self=['username'=>'test','display_name'=>'Administrador','role'=>'viewer','active'=>'1','password'=>''];
check(postForm($admin,'/admin/users/'.$initial,'/admin/users/'.$initial,$self)===422,'Proibe remover o proprio perfil admin');
$self['role']='admin';$self['active']='';check(postForm($admin,'/admin/users/'.$initial,'/admin/users/'.$initial,$self)===422,'Proibe desativar a propria conta');
$settings=json_decode(request($admin,'/api/settings')[1],true);unset($settings['version']);
$invalid=$settings;$invalid['refresh_seconds']=0;check(postForm($admin,'/admin/settings','/admin/settings',$invalid)===422,'Parametros invalidos rejeitados');
$settings['refresh_seconds']=20;check(in_array(postForm($admin,'/admin/settings','/admin/settings',$settings),[302,303]),'Configuracoes gravadas');
check(json_decode(request($viewer,'/api/settings')[1],true)['refresh_seconds']===20,'Configuracoes aplicadas no painel');
foreach([['a'],['c'],['a','c'],['a','b','c']] as $phases){
 $settings['active_phases']=$phases;
 check(in_array(postForm($admin,'/admin/settings','/admin/settings',$settings),[302,303]),'Salvar fases');
 check(json_decode(request($viewer,'/api/settings')[1],true)['active_phases']===$phases,'Fases persistidas e disponiveis a consulta');
 $form=request($admin,'/admin/settings')[1];check(str_contains($form,'name="active_phases[]"'),'Formulario oferece fases');
}
$invalid=$settings;$invalid['active_phases']=[];check(postForm($admin,'/admin/settings','/admin/settings',$invalid)===422,'Selecao vazia rejeitada');
$invalid['active_phases']=['d'];check(postForm($admin,'/admin/settings','/admin/settings',$invalid)===422,'Fase desconhecida rejeitada');
$settings['active_phases']=['a','b'];check(in_array(postForm($admin,'/admin/settings','/admin/settings',$settings),[302,303]),'Restaurar bifasico');
foreach(['kW','W'] as $unit){$settings['power_unit']=$unit;check(in_array(postForm($admin,'/admin/settings','/admin/settings',$settings),[302,303]),'Salvar unidade');check(json_decode(request($viewer,'/api/settings')[1],true)['power_unit']===$unit,'Unidade persistida');}
$invalid=$settings;$invalid['power_unit']='kWh';check(postForm($admin,'/admin/settings','/admin/settings',$invalid)===422,'Unidade invalida rejeitada');
$settings['flow_gauge_limit_w']=5000;check(in_array(postForm($admin,'/admin/settings','/admin/settings',$settings),[302,303]),'Salvar escala fixa');check(json_decode(request($viewer,'/api/settings')[1],true)['flow_gauge_limit_w']===5000,'Escala fixa persistida');
$invalid=$settings;$invalid['flow_gauge_limit_w']=0;check(postForm($admin,'/admin/settings','/admin/settings',$invalid)===422,'Escala zero rejeitada');
$audit=request($admin,'/admin/audit')[1];check(str_contains($audit,'settings.update')&&str_contains($audit,'user.create'),'Auditoria registrada');
check(!str_contains($audit,$person['password'])&&!str_contains($audit,'$2y$'),'Auditoria nao expoe senhas');
$self['active']='1';$self['password']='new-admin-password-2026';
check(in_array(postForm($admin,'/admin/users/'.$initial,'/admin/users/'.$initial,$self),[302,303]),'Administrador altera senha');
check(request($admin,'/api/readings')[0]===401,'Alteracao propria encerra sessao');
check(login($admin,'test','test-only-password')===401,'Senha antiga do .env nao permite contornar nova senha');
check(in_array(login($admin,'test',$self['password']),[302,303]),'Nova senha administrativa aceita');
echo "Administracao: cadastro, perfis, bloqueios, desativacao, sessoes, senhas, parametros e auditoria: OK\n";
