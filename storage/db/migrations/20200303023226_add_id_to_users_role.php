<?php

use Phinx\Db\Adapter\MysqlAdapter;

class AddIdToUsersRole extends Phinx\Migration\AbstractMigration
{
    public function change()
    {
        $this->table('user_roles', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
                'after' => 'users_id',
            ])
            ->removeIndexByName('roles_id')
            ->save();
    }
}
