<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class UpdateAppsColumns extends AbstractMigration
{
    public function change()
    {
        $this->table('apps', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'COMPACT',
            ])
        ->changeColumn('payments_active', 'integer', [
                'null' => true,
                'limit' => '10',
                'after' => 'is_actived',
            ])
        ->changeColumn('created_at', 'datetime', [
                'null' => false,
                'after' => 'payments_active',
            ])
        ->changeColumn('updated_at', 'datetime', [
                'null' => true,
                'after' => 'created_at',
            ])
        ->changeColumn('is_deleted', 'integer', [
                'null' => false,
                'default' => '0',
                'limit' => '10',
                'after' => 'updated_at',
            ])
            ->save();
    }
}
