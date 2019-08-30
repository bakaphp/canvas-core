<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class UpdateCustomFieldsValues extends AbstractMigration
{
    public function change()
    {
        $this->table('apps', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'COMPACT',
        ])
        ->changeColumn('key', 'string', [
            'null' => true,
            'limit' => 128,
            'collation' => 'utf8mb4_unicode_ci',
            'encoding' => 'utf8mb4',
            'after' => 'is_actived',
        ])
        ->changeColumn('payments_active', 'integer', [
            'null' => true,
            'limit' => '10',
            'after' => 'key',
        ])
            ->addColumn('ecosystem_login', 'integer', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'payments_active',
            ])
        ->changeColumn('is_public', 'integer', [
            'null' => true,
            'limit' => '10',
            'after' => 'ecosystem_login',
        ])
        ->changeColumn('created_at', 'datetime', [
            'null' => true,
            'after' => 'is_public',
        ])
        ->changeColumn('updated_at', 'datetime', [
            'null' => true,
            'after' => 'created_at',
        ])
        ->changeColumn('is_deleted', 'integer', [
            'null' => true,
            'limit' => '10',
            'after' => 'updated_at',
        ])
            ->save();
        $this->table('users_associated_apps', [
            'id' => false,
            'primary_key' => ['users_id', 'apps_id', 'companies_id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addColumn('password', 'string', [
                'null' => true,

                'limit' => 255,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'user_role',
            ])
        ->changeColumn('created_at', 'datetime', [
            'null' => false,
            'after' => 'password',
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
            ->save();
        $this->table('custom_fields_values', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
        ->changeColumn('value', 'text', [
            'null' => false,
            'limit' => MysqlAdapter::TEXT_LONG,
            'collation' => 'utf8mb4_unicode_ci',
            'encoding' => 'utf8mb4',
            'after' => 'label',
        ])
            ->save();
    }
}
