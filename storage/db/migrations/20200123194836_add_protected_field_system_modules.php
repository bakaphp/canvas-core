<?php

use Phinx\Db\Adapter\MysqlAdapter;

class AddProtectedFieldSystemModules extends Phinx\Migration\AbstractMigration
{
    public function change()
    {
        $this->table('system_modules', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => 'list of modules , user can interact on each of the diff apps',
            'row_format' => 'DYNAMIC',
        ])
        ->addColumn('protected', 'boolean', [
            'null' => false,
            'default' => '0',
            'limit' => MysqlAdapter::INT_TINY,
            'after' => 'mobile_tab_index',
        ])
            ->save();
    }
}
