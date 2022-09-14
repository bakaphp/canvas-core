<?php

use Phinx\Db\Adapter\MysqlAdapter;

class UserInviteAdditionalInfo extends Phinx\Migration\AbstractMigration
{
    public function change()
    {
        $this->table('users', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->changeColumn('email', 'string', [
                'null' => false,
                'limit' => 255,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'uuid',
            ])
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
            ->addColumn('companies_branches_id', 'integer', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'companies_id',
            ])
            ->changeColumn('role_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'companies_branches_id',
            ])
            ->changeColumn('apps_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'role_id',
            ])
            ->changeColumn('email', 'string', [
                'null' => false,
                'limit' => 255,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'apps_id',
            ])
            ->addColumn('firstname', 'string', [
                'null' => true,
                'limit' => 64,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'email',
            ])
            ->addColumn('lastname', 'string', [
                'null' => true,
                'limit' => 64,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'firstname',
            ])
            ->addColumn('description', 'text', [
                'null' => true,
                'limit' => 65535,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'lastname',
            ])
            ->changeColumn('created_at', 'datetime', [
                'null' => false,
                'after' => 'description',
            ])
            ->changeColumn('updated_at', 'datetime', [
                'null' => true,
                'after' => 'created_at',
            ])
            ->changeColumn('is_deleted', 'integer', [
                'null' => false,
                'default' => '0',
                'limit' => '3',
                'after' => 'updated_at',
            ])
            ->addIndex(['companies_branches_id'], [
                'name' => 'companies_branches_id',
                'unique' => false,
            ])
            ->save();
    }
}
