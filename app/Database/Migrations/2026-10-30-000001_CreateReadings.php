<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Creates the application schema inside an existing MySQL database. */
class CreateReadings extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'received_at' => ['type' => 'VARCHAR', 'constraint' => 40],
            'source' => ['type' => 'VARCHAR', 'constraint' => 255],
            'method' => ['type' => 'VARCHAR', 'constraint' => 16],
            'content_type' => ['type' => 'VARCHAR', 'constraint' => 255],
            'query' => ['type' => 'TEXT'],
            'body' => ['type' => 'LONGBLOB'],
            'parsed' => ['type' => 'LONGTEXT'],
            'import_key' => ['type' => 'CHAR', 'constraint' => 64, 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('import_key', 'uq_import_key');
        $this->forge->addKey(['received_at', 'id'], false, false, 'idx_received_at');
        // Preserve tables/data already created through the SQL import.
        $this->forge->createTable('readings', true, [
            'ENGINE' => 'InnoDB',
            'DEFAULT CHARACTER SET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('readings', true);
    }
}
