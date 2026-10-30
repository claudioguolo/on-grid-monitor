<?php
namespace App\Controllers;
use App\Libraries\PanelAuth;
class Auth extends BaseController
{
    public function login()
    {
        if(PanelAuth::signedIn())return redirect()->to(PanelAuth::appPath().'/');
        helper('form');
        $this->response->setHeader('Cache-Control','no-store')->setHeader('X-Frame-Options','DENY');
        $configured=PanelAuth::configured();$error='';
        if(!$configured){$this->response->setStatusCode(503);$error='O acesso ao painel ainda não foi configurado. Contate o administrador.';}
        return $this->response->setBody(view('login',compact('error','configured')));
    }
    public function authenticate()
    {
        helper('form');$configured=PanelAuth::configured();
        $key='panel-login-'.hash('sha256',$this->request->getIPAddress());
        $allowed=service('throttler')->check($key,10,60);
        $user=$this->request->getPost('username');$password=$this->request->getPost('password');
        if($configured&&$allowed&&is_string($user)&&is_string($password)&&strlen($user)<=255&&strlen($password)<=1024&&PanelAuth::verify($user,$password)){
            $session=session();$session->regenerate(true);
            $session->set('panel_identity',['signature'=>PanelAuth::signature(),'expires_at'=>time()+config('Session')->expiration]);
            return redirect()->to(PanelAuth::appPath().'/')->withCookies()->setHeader('Cache-Control','no-store');
        }
        $error=$allowed?'Usuário ou senha inválidos.':'Muitas tentativas. Aguarde um minuto e tente novamente.';
        return $this->response->setStatusCode($allowed?401:429)->setHeader('Cache-Control','no-store')
            ->setBody(view('login',compact('error','configured')));
    }
    public function logout()
    {
        session()->destroy();
        return redirect()->to(PanelAuth::appPath().'/login')->withCookies()->setHeader('Cache-Control','no-store');
    }
}
