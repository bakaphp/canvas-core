<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class UpdateAsociationTables extends AbstractMigration
{
    public function change()
    {
        $this->table('users_associated_company', [
            'id' => false,
            'primary_key' => ['users_id', 'companies_id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'COMPACT',
        ])
            ->addColumn('created_at', 'datetime', [
                'null' => false,
                'after' => 'user_role',
            ])
            ->addColumn('updated_at', 'datetime', [
                'null' => true,
                'after' => 'created_at',
            ])
            ->addColumn('is_deleted', 'integer', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'updated_at',
            ])
            ->save();
        $this->table('notification_types', [
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
                'after' => 'id',
            ])
            ->addColumn('system_modules_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'apps_id',
            ])
        ->changeColumn('name', 'string', [
            'null' => false,
            'limit' => 64,
            'collation' => 'utf8mb4_unicode_ci',
            'encoding' => 'utf8mb4',
            'after' => 'system_modules_id',
        ])
            ->addColumn('key', 'string', [
                'null' => false,
                'limit' => 50,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'name',
            ])
            ->addColumn('description', 'text', [
                'null' => false,
                'limit' => 65535,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'key',
            ])
            ->addColumn('icon_url', 'string', [
                'null' => true,
                'limit' => 50,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'description',
            ])
            ->addColumn('with_realtime', 'integer', [
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
            'null' => true
            'after' => 'created_at',
        ])
        ->changeColumn('is_deleted', 'integer', [
            'null' => false,
            'default' => '0',
            'limit' => '3',
            'after' => 'updated_at',
        ])
        ->addIndex(['apps_id'], [
            'name' => 'apps_id',
            'unique' => false,
        ])
        ->addIndex(['system_modules_id'], [
            'name' => 'system_modules_id',
            'unique' => false,
        ])
        ->addIndex(['apps_id', 'system_modules_id'], [
            'name' => 'apps_id_system_modules_id',
            'unique' => false,
        ])
        ->addIndex(['with_realtime'], [
            'name' => 'with_realtime',
            'unique' => false,
        ])
        ->addIndex(['key'], [
            'name' => 'key',
            'unique' => false,
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
            ->addColumn('created_at', 'datetime', [
                'null' => false,
                'after' => 'user_role',
            ])
            ->addColumn('updated_at', 'datetime', [
                'null' => true,
                'after' => 'created_at',
            ])
            ->addColumn('is_deleted', 'integer', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'updated_at',
            ])
        ->addIndex(['identify_id'], [
            'name' => 'identify_id',
            'unique' => false,
        ])
            ->save();
        $this->table('system_modules', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => 'list of modules , user can interact on each of the diff apps',
            'row_format' => 'DYNAMIC',
        ])
        ->changeColumn('id', 'integer', [
            'null' => false,
            'limit' => '10',
            'identity' => 'enable',
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
        ->changeColumn('content', 'text', [
            'null' => false,
            'limit' => MysqlAdapter::TEXT_LONG,
            'collation' => 'utf8mb4_unicode_ci',
            'encoding' => 'utf8mb4',
            'after' => 'entity_id',
        ])
            ->addColumn('read', 'integer', [
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
            ->save();
    }
}
