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
        ->addIndex(['roles_id'], [
                'name' => 'roles_id',
                'unique' => false,
            ])
            ->save();
    }
}
