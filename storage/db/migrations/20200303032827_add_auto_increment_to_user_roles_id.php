<?php

use Phinx\Db\Adapter\MysqlAdapter;

class AddAutoIncrementToUserRolesId extends Phinx\Migration\AbstractMigration
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
        ->changeColumn('id', 'integer', [
                'null' => false,
                'limit' => '10',
                'identity' => 'enable',
            ])
            ->save();
    }
}
