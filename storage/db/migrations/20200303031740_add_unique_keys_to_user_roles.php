<?php

use Phinx\Db\Adapter\MysqlAdapter;

class AddUniqueKeysToUserRoles extends Phinx\Migration\AbstractMigration
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
            ])
        ->changeColumn('users_id', 'integer', [
                'null' => false,
                'limit' => '10',
                'after' => 'id',
            ])
        ->changeColumn('roles_id', 'integer', [
                'null' => false,
                'limit' => '10',
                'after' => 'users_id',
            ])
        ->changeColumn('apps_id', 'integer', [
                'null' => false,
                'limit' => '10',
                'after' => 'roles_id',
            ])
        ->changeColumn('companies_id', 'integer', [
                'null' => false,
                'limit' => '10',
                'after' => 'apps_id',
            ])
        ->changeColumn('created_at', 'datetime', [
                'null' => false,
                'after' => 'companies_id',
            ])
        ->changeColumn('updated_at', 'datetime', [
                'null' => true,
                'after' => 'created_at',
            ])
        ->changeColumn('is_deleted', 'boolean', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'updated_at',
            ])
        ->addIndex(['users_id', 'apps_id', 'companies_id'], [
                'name' => 'user_roles_unique',
                'unique' => true,
            ])
            ->save();
    }
}
