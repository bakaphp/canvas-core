<?php

use Phinx\Db\Adapter\MysqlAdapter;

class RemoveCompaniesMenu extends Phinx\Migration\AbstractMigration
{
    public function change()
    {
        $this->table('menus', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->removeColumn('companies_id')
            ->save();

        $this->table('users_invite', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addColumn('apps_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'role_id',
            ])
            ->removeColumn('app_id')
            ->removeIndexByName('app_id')
            ->addIndex(['apps_id'], [
                'name' => 'app_id',
                'unique' => false,
            ])
            ->save();
    }
}
