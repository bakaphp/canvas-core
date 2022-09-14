<?php

use Phinx\Db\Adapter\MysqlAdapter;

class UserAssociatedCompanyBranch extends Phinx\Migration\AbstractMigration
{
    public function change()
    {
        $this->table('users_associated_company', [
            'id' => false,
            'primary_key' => ['users_id', 'companies_id', 'companies_branches_id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'COMPACT',
        ])
            ->addColumn('companies_branches_id', 'integer', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'companies_id',
            ])
            ->changeColumn('identify_id', 'string', [
                'null' => true,
                'limit' => 45,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'companies_branches_id',
            ])
            ->changeColumn('user_active', 'boolean', [
                'null' => false,
                'default' => '1',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'identify_id',
            ])
            ->changeColumn('user_role', 'string', [
                'null' => true,
                'limit' => 45,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'user_active',
            ])
            ->changeColumn('created_at', 'datetime', [
                'null' => false,
                'after' => 'user_role',
            ])
            ->changeColumn('updated_at', 'datetime', [
                'null' => true,
                'after' => 'created_at',
            ])
            ->changeColumn('is_deleted', 'integer', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'updated_at',
            ])
            ->removeIndexByName('users_id')
            ->addIndex(['users_id', 'companies_branches_id', 'companies_id'], [
                'name' => 'users_id',
                'unique' => true,
            ])
            ->addIndex(['companies_branches_id'], [
                'name' => 'companies_branches_id',
                'unique' => false,
            ])
            ->addIndex(['user_role'], [
                'name' => 'user_role',
                'unique' => false,
            ])
            ->addIndex(['identify_id'], [
                'name' => 'identify_id',
                'unique' => false,
            ])
            ->save();
        $this->table('companies_groups', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->changeColumn('uuid', 'char', [
                'null' => true,
                'limit' => 36,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'id',
            ])
            ->removeIndexByName('uuid')
            ->addIndex(['uuid'], [
                'name' => 'uuid',
                'unique' => false,
            ])
            ->save();
    }
}
