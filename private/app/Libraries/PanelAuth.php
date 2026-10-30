<?php
namespace App\Libraries;
final class PanelAuth
{
    public static function appPath(): string {return rtrim((string)parse_url(base_url(),PHP_URL_PATH),'/');}
    public static function credentials(): array
    {
        return [(string)env('DASHBOARD_USERNAME',env('dashboard.username','')),
            (string)env('DASHBOARD_PASSWORD',env('dashboard.password','')),
            (string)env('DASHBOARD_PASSWORD_HASH',env('dashboard.passwordHash',''))];
    }
    public static function seedAdmin(): void
    {
        [$username,$plain,$hash]=self::credentials();$db=db_connect();
        if($username===''||($plain===''&&$hash==='')||!$db->tableExists('users')||$db->table('users')->countAllResults()>0)return;
        $key='energia-admin-'.sha1($db->getDatabase());
        if((int)$db->query('SELECT GET_LOCK(?,10) AS acquired',[$key])->getRowArray()['acquired']!==1)throw new \RuntimeException('Inicializacao de administrador em andamento');
        try{
            if($db->table('users')->countAllResults()===0){
                $now=gmdate('Y-m-d\TH:i:s+00:00');
                $db->table('users')->insert(['username'=>$username,'display_name'=>'Administrador',
                    'password_hash'=>$hash!==''?$hash:password_hash($plain,PASSWORD_DEFAULT),
                    'role'=>'admin','active'=>1,'auth_version'=>1,'created_at'=>$now,'updated_at'=>$now]);
            }
        }finally{$db->query('SELECT RELEASE_LOCK(?)',[$key]);}
    }
    public static function configured(): bool
    {
        self::seedAdmin();return db_connect()->table('users')->countAllResults()>0;
    }
    public static function current(): ?array
    {
        $identity=session()->get('panel_identity');
        if(!is_array($identity)||!is_int($identity['expires_at']??null)||$identity['expires_at']<=time()||!isset($identity['user_id'],$identity['auth_version']))return null;
        $user=db_connect()->table('users')->select('id,username,display_name,role,active,auth_version')->where('id',$identity['user_id'])->get()->getRowArray();
        if(!$user||(int)$user['active']!==1||(int)$user['auth_version']!==(int)$identity['auth_version'])return null;
        return $user;
    }
    public static function signedIn(): bool {return self::current()!==null;}
    public static function isAdmin(): bool {return (self::current()['role']??null)==='admin';}
    public static function authenticate(string $username,string $password): ?array
    {
        self::seedAdmin();$user=db_connect()->table('users')->where('username',$username)->get()->getRowArray();
        // Always perform a password check, even for an unknown account.
        $hash=$user['password_hash']??'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.';
        if(!password_verify($password,$hash)||!$user||(int)$user['active']!==1)return null;
        return $user;
    }
}
