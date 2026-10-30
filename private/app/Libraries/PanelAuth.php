<?php
namespace App\Libraries;
use CodeIgniter\HTTP\RequestInterface;
final class PanelAuth
{
    public static function credentials(): array
    {
        return [
            (string)env('DASHBOARD_USERNAME',env('dashboard.username','')),
            (string)env('DASHBOARD_PASSWORD',env('dashboard.password','')),
            (string)env('DASHBOARD_PASSWORD_HASH',env('dashboard.passwordHash','')),
        ];
    }
    public static function configured(): bool
    {
        [$user,$plain,$hash]=self::credentials();
        return $user!==''&&($plain!==''||$hash!=='');
    }
    public static function signature(): string
    {
        return hash('sha256',json_encode(self::credentials(),JSON_THROW_ON_ERROR));
    }
    public static function signedIn(): bool
    {
        if(!self::configured())return false;
        $session=session();$identity=$session->get('panel_identity');
        return is_array($identity)&&is_int($identity['expires_at']??null)&&$identity['expires_at']>time()&&is_string($identity['signature']??null)
            &&hash_equals(self::signature(),$identity['signature']);
    }
    public static function verify(string $user,string $password): bool
    {
        [$expected,$plain,$hash]=self::credentials();
        $valid=$hash!==''?password_verify($password,$hash):($plain!==''&&hash_equals($plain,$password));
        return self::configured()&&hash_equals($expected,$user)&&$valid;
    }
    public static function appPath(): string
    {
        return rtrim((string)parse_url(base_url(),PHP_URL_PATH),'/');
    }
}
