<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class AddThreeNewColumnsOnSystemModulesTable extends AbstractMigration
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
        ->addColumn('component_type', 'string', [
                'null' => true,
                'limit' => 64,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'is_deleted',
        ])
        ->addColumn('navigation_type', 'string', [
            'null' => true,
            'limit' => 64,
            'collation' => 'utf8mb4_unicode_ci',
            'encoding' => 'utf8mb4',
            'after' => 'name',
        ])
        ->addColumn('tab_index', 'integer', [
                'null' => true,
                'default' => '0',
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'navigation_type',
            ])
        ->save();
    }
}
