<?php
namespace App\Libraries;
final class AdminStore
{
    private static function authorize($db,int $actor): void
    {
        $db->query('SELECT id FROM users WHERE role=? AND active=1 ORDER BY id FOR UPDATE',['admin'])->getResultArray();
        $user=$db->table('users')->where('id',$actor)->get()->getRowArray();
        if(!$user||$user['role']!=='admin'||(int)$user['active']!==1)throw new \InvalidArgumentException('Acesso administrativo indisponível. Entre novamente.');
    }
    private static function audit($db,int $actor,string $action,?int $target,array $details): void
    {
        $db->table('admin_audit')->insert(['actor_id'=>$actor,'action'=>$action,'target_id'=>$target,
            'details'=>json_encode($details,JSON_THROW_ON_ERROR|JSON_UNESCAPED_UNICODE),'created_at'=>gmdate('Y-m-d\TH:i:s+00:00')]);
    }
    public static function saveUser(int $actor,?int $id,array $data): int
    {
        $username=trim($data['username']??'');$name=trim($data['display_name']??'');$role=$data['role']??'';$password=$data['password']??'';$active=($data['active']??'')==='1';
        if(!preg_match('/^[a-zA-Z0-9._@-]{3,64}$/D',$username))throw new \InvalidArgumentException('Usuário deve ter de 3 a 64 caracteres: letras, números, ponto, @, hífen ou sublinhado.');
        if($name===''||mb_strlen($name)>150)throw new \InvalidArgumentException('Informe um nome com até 150 caracteres.');
        if(!in_array($role,['admin','viewer'],true))throw new \InvalidArgumentException('Perfil inválido.');
        if(($id===null||$password!=='')&&(mb_strlen($password)<12||strlen($password)>72))throw new \InvalidArgumentException('A senha deve ter pelo menos 12 caracteres e até 72 bytes.');
        $db=db_connect();$db->transException(true)->transBegin();
        try{
            self::authorize($db,$actor);$current=$id?$db->table('users')->where('id',$id)->get()->getRowArray():null;
            if($id&&!$current)throw new \InvalidArgumentException('Usuário não encontrado.');
            if($id===$actor&&(!$active||$role!=='admin'))throw new \InvalidArgumentException('Você não pode desativar sua própria conta nem remover seu próprio perfil administrativo.');
            $duplicate=$db->table('users')->where('username',$username);if($id)$duplicate->where('id !=',$id);
            if($duplicate->countAllResults())throw new \InvalidArgumentException('Esse usuário já está cadastrado.');
            if($current&&$current['role']==='admin'&&(int)$current['active']===1&&(!$active||$role!=='admin')&&$db->table('users')->where('role','admin')->where('active',1)->countAllResults()<=1)throw new \InvalidArgumentException('Mantenha pelo menos um administrador ativo.');
            $now=gmdate('Y-m-d\TH:i:s+00:00');$row=['username'=>$username,'display_name'=>$name,'role'=>$role,'active'=>$active?1:0,'updated_at'=>$now];
            if($password!=='')$row['password_hash']=password_hash($password,PASSWORD_DEFAULT);
            if($id){$row['auth_version']=(int)$current['auth_version']+1;$db->table('users')->where('id',$id)->update($row);}
            else{$row['created_at']=$now;$row['auth_version']=1;$db->table('users')->insert($row);$id=(int)$db->insertID();}
            self::audit($db,$actor,$current?'user.update':'user.create',$id,['username'=>$username,'role'=>$role,'active'=>$active,'password_changed'=>$password!=='']);
            if(!$db->transStatus())throw new \RuntimeException('Falha ao salvar usuário');$db->transCommit();return $id;
        }catch(\Throwable $e){$db->transRollback();throw $e;}
    }
    public static function saveSettings(int $actor,array $settings): void
    {
        Parameters::validate($settings);$db=db_connect();$db->transException(true)->transBegin();
        try{
            self::authorize($db,$actor);$row=['id'=>1,'settings_json'=>json_encode($settings,JSON_THROW_ON_ERROR),'updated_at'=>gmdate('Y-m-d\TH:i:s+00:00')];
            if($db->table('system_settings')->where('id',1)->countAllResults())$db->table('system_settings')->where('id',1)->update($row);
            else $db->table('system_settings')->insert($row);
            self::audit($db,$actor,'settings.update',null,$settings);
            if(!$db->transStatus())throw new \RuntimeException('Falha ao salvar configurações');$db->transCommit();
        }catch(\Throwable $e){$db->transRollback();throw $e;}
    }
}
