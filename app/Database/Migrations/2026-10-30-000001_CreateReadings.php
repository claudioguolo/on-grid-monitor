<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;
class CreateReadings extends Migration
{
    public function up() { $this->db->query(file_get_contents(ROOTPATH . 'database/schema.sql')); }
    public function down() { $this->forge->dropTable('readings', true); }
}
