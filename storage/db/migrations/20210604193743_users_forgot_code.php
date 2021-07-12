<?php

use Phinx\Db\Adapter\MysqlAdapter;

class UsersForgotCode extends Phinx\Migration\AbstractMigration
{
    public function change()
    {
        $this->table('users', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
        ->addColumn('user_recover_code', 'integer', [
            'null' => true,
            'default' => null,
            'limit' => MysqlAdapter::INT_REGULAR,
            'after' => 'zip_code',
        ])
        ->save();
    }
}
