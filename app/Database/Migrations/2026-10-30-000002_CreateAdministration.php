<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;
class CreateAdministration extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'=>['type'=>'BIGINT','unsigned'=>true,'auto_increment'=>true],
            'username'=>['type'=>'VARCHAR','constraint'=>191],
            'display_name'=>['type'=>'VARCHAR','constraint'=>150],
            'password_hash'=>['type'=>'VARCHAR','constraint'=>255],
            'role'=>['type'=>'VARCHAR','constraint'=>16],
            'active'=>['type'=>'TINYINT','constraint'=>1,'default'=>1],
            'auth_version'=>['type'=>'INT','unsigned'=>true,'default'=>1],
            'created_at'=>['type'=>'VARCHAR','constraint'=>40],
            'updated_at'=>['type'=>'VARCHAR','constraint'=>40],
        ]);
        $this->forge->addKey('id',true);$this->forge->addUniqueKey('username','uq_users_username');
        $this->forge->createTable('users',true,['ENGINE'=>'InnoDB']);
        $this->forge->addField(['id'=>['type'=>'INT','unsigned'=>true],'settings_json'=>['type'=>'TEXT'],'updated_at'=>['type'=>'VARCHAR','constraint'=>40]]);
        $this->forge->addKey('id',true);$this->forge->createTable('system_settings',true,['ENGINE'=>'InnoDB']);
        $this->forge->addField([
            'id'=>['type'=>'BIGINT','unsigned'=>true,'auto_increment'=>true],
            'actor_id'=>['type'=>'BIGINT','unsigned'=>true],
            'action'=>['type'=>'VARCHAR','constraint'=>50],
            'target_id'=>['type'=>'BIGINT','unsigned'=>true,'null'=>true],
            'details'=>['type'=>'TEXT'],'created_at'=>['type'=>'VARCHAR','constraint'=>40],
        ]);
        $this->forge->addKey('id',true);$this->forge->createTable('admin_audit',true,['ENGINE'=>'InnoDB']);
        \App\Libraries\PanelAuth::seedAdmin();
    }
    public function down()
    {
        $this->forge->dropTable('admin_audit',true);$this->forge->dropTable('system_settings',true);$this->forge->dropTable('users',true);
    }
}
