<?php
namespace App\Controllers;
use App\Libraries\PanelAuth;
use App\Libraries\Parameters;
use App\Libraries\AdminStore;
class Admin extends BaseController
{
    private function deny()
    {
        if(!PanelAuth::signedIn())return redirect()->to(PanelAuth::appPath().'/login');
        if(!PanelAuth::isAdmin())return $this->response->setStatusCode(403)->setBody('Acesso restrito a administradores.');
        return null;
    }
    private function page(array $data)
    {
        helper('form');$data+=['actor'=>PanelAuth::current(),'appPath'=>PanelAuth::appPath(),'error'=>'','success'=>session()->getFlashdata('success')??''];
        return $this->response->setHeader('Cache-Control','no-store')->setBody(view('admin',$data));
    }
    public function users()
    {
        if($denied=$this->deny())return $denied;
        $users=db_connect()->table('users')->select('id,username,display_name,role,active')->orderBy('id')->get()->getResultArray();
        return $this->page(['tab'=>'users','users'=>$users]);
    }
    public function edit($id=null)
    {
        if($denied=$this->deny())return $denied;
        $user=$id?db_connect()->table('users')->select('id,username,display_name,role,active')->where('id',(int)$id)->get()->getRowArray():['id'=>null,'username'=>'','display_name'=>'','role'=>'viewer','active'=>1];
        if(!$user)return $this->response->setStatusCode(404)->setBody('Usuário não encontrado.');
        return $this->page(['tab'=>'user-form','user'=>$user]);
    }
    public function saveUser($id=null)
    {
        if($denied=$this->deny())return $denied;
        $actor=PanelAuth::current();$data=[];
        foreach(['username','display_name','password','role','active'] as $key){$value=$this->request->getPost($key);if($value!==null&&!is_string($value))return $this->response->setStatusCode(400)->setBody('Dados inválidos.');$data[$key]=$value??'';}
        try{
            AdminStore::saveUser((int)$actor['id'],$id?(int)$id:null,$data);
            if($id&&(int)$id===(int)$actor['id']){session()->destroy();return redirect()->to(PanelAuth::appPath().'/login')->withCookies();}
            return redirect()->to(PanelAuth::appPath().'/admin/users')->with('success','Usuário salvo. Alterações encerram as sessões anteriores dessa conta.')->withCookies();
        }catch(\InvalidArgumentException $e){
            $this->response->setStatusCode(422);return $this->page(['tab'=>'user-form','user'=>['id'=>$id,'username'=>$data['username'],'display_name'=>$data['display_name'],'role'=>$data['role'],'active'=>$data['active']==='1'],'error'=>$e->getMessage()]);
        }
    }
    public function settings()
    {
        if($denied=$this->deny())return $denied;
        return $this->page(['tab'=>'settings','settings'=>Parameters::load()]);
    }
    public function saveSettings()
    {
        if($denied=$this->deny())return $denied;
        $settings=[];
        try{
            $zone=$this->request->getPost('timezone');if(!is_string($zone))throw new \InvalidArgumentException('Fuso inválido.');$settings['timezone']=$zone;
            foreach(['stale_seconds','max_payload_bytes','history_limit','default_chart_hours','refresh_seconds'] as $key){$value=$this->request->getPost($key);if(!is_string($value)||!ctype_digit($value))throw new \InvalidArgumentException('Informe números inteiros válidos.');$settings[$key]=(int)$value;}
            $settings['active_phases']=$this->request->getPost('active_phases')??[];
            AdminStore::saveSettings((int)PanelAuth::current()['id'],$settings);
            return redirect()->to(PanelAuth::appPath().'/admin/settings')->with('success','Configurações salvas.')->withCookies();
        }catch(\Exception $e){
            if($e instanceof \CodeIgniter\Database\Exceptions\DatabaseException)throw $e;
            $this->response->setStatusCode(422);return $this->page(['tab'=>'settings','settings'=>Parameters::load(),'error'=>'Configurações inválidas. Selecione ao menos uma fase (A, B ou C) e confira os limites e o fuso horário.']);
        }
    }
    public function audit()
    {
        if($denied=$this->deny())return $denied;
        $logs=db_connect()->table('admin_audit a')->select('a.action,a.target_id,a.details,a.created_at,u.username')->join('users u','u.id=a.actor_id','left')->orderBy('a.id','DESC')->limit(50)->get()->getResultArray();
        return $this->page(['tab'=>'audit','logs'=>$logs]);
    }
}
