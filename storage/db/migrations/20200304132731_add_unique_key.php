<?php

use Phinx\Db\Adapter\MysqlAdapter;

class AddUniqueKey extends Phinx\Migration\AbstractMigration
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
        ->addIndex(['users_id', 'apps_id', 'companies_id'], [
                'name' => 'user_roles_UN',
                'unique' => true,
            ])
            ->save();
    }
}
