<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class AddNotificationTypeRealTime extends AbstractMigration
{
    public function change()
    {
        $stmt = $this->query('SHOW COLUMNS FROM notification_types ');

        $rows = $stmt->fetchAll();

        $hasColumn = false;

        //verify this columns, how did you get them? who knows
        //this is for old instances of kanvas
        foreach ($rows as $row) {
            if (in_array($row['Field'], ['key', 'with_realtime'])) {
                $hasColumn = true;
            }
        }
        //work around for old canvas instance running with the old migration
        if (!$hasColumn) {
            $this->table('notification_types', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('key', 'string', [
                'null' => false,
                'limit' => 50,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'name',
            ])
            ->changeColumn('description', 'text', [
                'null' => false,
                'limit' => 65535,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'key',
            ])
            ->changeColumn('icon_url', 'string', [
                'null' => true,
                'default' => 'NULL',
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
                'null' => true,
                'after' => 'created_at',
            ])
            ->changeColumn('is_deleted', 'integer', [
                'null' => false,
                'default' => '0',
                'limit' => '3',
                'after' => 'updated_at',
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
        }

        $stmt = $this->query('SHOW COLUMNS FROM notifications ');

        $rows = $stmt->fetchAll();

        $hasColumn = false;

        //verify this columns, how did you get them? who knows
        //this is for old instances of kanvas
        foreach ($rows as $row) {
            if (in_array($row['Field'], ['read'])) {
                $hasColumn = true;
            }
        }

        if (!$hasColumn) {
            $this->table('notifications', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
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
}
