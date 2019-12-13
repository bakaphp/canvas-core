<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class UpdateNofiticationTables extends AbstractMigration
{
    public function change()
    {
        $this->table('notification_types', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
        ->changeColumn('key', 'string', [
            'null' => false,
            'limit' => 255,
            'collation' => 'utf8mb4_unicode_ci',
            'encoding' => 'utf8mb4',
            'after' => 'name',
        ])
        ->changeColumn('description', 'text', [
            'null' => true,
            'limit' => 65535,
            'collation' => 'utf8mb4_unicode_ci',
            'encoding' => 'utf8mb4',
            'after' => 'key',
        ])
            ->addColumn('template', 'text', [
                'null' => true,
                'limit' => 65535,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'description',
            ])
        ->changeColumn('icon_url', 'string', [
            'null' => true,
            'limit' => 50,
            'collation' => 'utf8mb4_unicode_ci',
            'encoding' => 'utf8mb4',
            'after' => 'template',
        ])
        ->changeColumn('with_realtime', 'integer', [
            'null' => false,
            'default' => '0',
            'limit' => MysqlAdapter::INT_TINY,
            'after' => 'icon_url',
        ])
        ->changeColumn('created_at', 'datetime', [
            'null' => false,
            'after' => 'with_realtime',
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
            ->save();

        $this->table('notifications', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addColumn('from_users_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'users_id',
            ])
        ->changeColumn('companies_id', 'integer', [
            'null' => false,
            'limit' => MysqlAdapter::INT_REGULAR,
            'after' => 'from_users_id',
        ])
        ->changeColumn('apps_id', 'integer', [
            'null' => false,
            'limit' => MysqlAdapter::INT_REGULAR,
            'after' => 'companies_id',
        ])
            ->addColumn('system_modules_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'apps_id',
            ])
        ->changeColumn('notification_type_id', 'integer', [
            'null' => false,
            'limit' => MysqlAdapter::INT_REGULAR,
            'after' => 'system_modules_id',
        ])
        ->changeColumn('entity_id', 'integer', [
            'null' => false,
            'limit' => MysqlAdapter::INT_REGULAR,
            'after' => 'notification_type_id',
        ])
        ->changeColumn('content', 'text', [
            'null' => false,
            'limit' => MysqlAdapter::TEXT_LONG,
            'collation' => 'utf8mb4_unicode_ci',
            'encoding' => 'utf8mb4',
            'after' => 'entity_id',
        ])
        ->changeColumn('read', 'integer', [
            'null' => false,
            'default' => '0',
            'limit' => MysqlAdapter::INT_TINY,
            'after' => 'content',
        ])
        ->changeColumn('created_at', 'datetime', [
            'null' => false,
            'after' => 'read',
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
            ->removeColumn('system_module_id')
        ->addIndex(['system_modules_id'], [
            'name' => 'notification_system_module_id',
            'unique' => false,
        ])
        ->addIndex(['from_users_id'], [
            'name' => 'from_users_id',
            'unique' => false,
        ])
            ->save();
    }
}
