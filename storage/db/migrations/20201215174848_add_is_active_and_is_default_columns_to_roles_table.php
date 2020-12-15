<?php

use Phinx\Db\Adapter\MysqlAdapter;

class AddIsActiveAndIsDefaultColumnsToRolesTable extends Phinx\Migration\AbstractMigration
{
    public function change()
    {
        $this->execute("ALTER DATABASE CHARACTER SET 'utf8';");
        $this->execute("ALTER DATABASE COLLATE='utf8_general_mysql500_ci';");
        $this->table('roles', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('is_default', 'integer', [
                'null' => false,
                'default' => '0',
                'limit' => '1',
                'after' => 'updated_at',
            ])
            ->addColumn('is_active', 'integer', [
                'null' => false,
                'default' => '1',
                'limit' => '1',
                'after' => 'is_default',
            ])
            ->changeColumn('is_deleted', 'integer', [
                'null' => false,
                'default' => '0',
                'limit' => '3',
                'after' => 'is_active',
            ])
            
            ->save();
    }
}
