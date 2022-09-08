<?php

use Phinx\Db\Adapter\MysqlAdapter;

class AddUserAssociationConfig extends Phinx\Migration\AbstractMigration
{
    public function change()
    {
        $this->table('users_associated_apps', [
            'id' => false,
            'primary_key' => ['users_id', 'apps_id', 'companies_id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addColumn('configuration', 'text', [
                'null' => true,
                'default' => null,
                'limit' => MysqlAdapter::TEXT_LONG,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'password',
            ])
            ->addIndex(['user_role'], [
                'name' => 'user_role',
                'unique' => false,
            ])
            ->save();
        $this->table('users_associated_company', [
            'id' => false,
            'primary_key' => ['users_id', 'companies_id', 'companies_branches_id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'COMPACT',
        ])
            ->addColumn('configuration', 'text', [
                'null' => true,
                'default' => null,
                'limit' => MysqlAdapter::TEXT_LONG,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'user_role',
            ])
            ->save();
    }
}
