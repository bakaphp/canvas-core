<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class UpdateNotificationTypeTable extends AbstractMigration
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
            ->addColumn('description', 'text', [
                'null' => false,
                'limit' => 65535,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'name',
            ])
            ->addColumn('icon_url', 'string', [
                'null' => true,
                'default' => 'NULL',
                'limit' => 50,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'description',
            ])
        ->changeColumn('created_at', 'datetime', [
            'null' => false,
            'after' => 'icon_url',
        ])
        ->changeColumn('updated_at', 'datetime', [
            'null' => true,
            'default' => 'NULL',
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
            ->save();
    }
}
