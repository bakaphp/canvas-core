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
        ->changeColumn('name', 'string', [
            'null' => false,
            'limit' => 64,
            'collation' => 'utf8mb4_unicode_ci',
            'encoding' => 'utf8mb4',
            'after' => 'system_modules_id',
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
        $this->table('users_associated_apps', [
            'id' => false,
            'primary_key' => ['users_id', 'companies_id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
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
