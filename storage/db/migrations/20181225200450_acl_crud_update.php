<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class AclCrudUpdate extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('roles_inherits', [
                'id' => false,
                'primary_key' => ['roles_id', 'roles_inherit'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_520_ci',
                'comment' => '',
                'row_format' => 'Dynamic',
            ]);
        $table->addColumn('roles_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'precision' => '10',
            ])
        ->changeColumn('roles_inherit', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'precision' => '10',
                'after' => 'roles_id',
            ])
            ->save();
        

    }
}
