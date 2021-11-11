<?php

use Phinx\Db\Adapter\MysqlAdapter;

class UpdateupdateGroupNotifications extends Phinx\Migration\AbstractMigration
{
    public function change()
    {
        $this->table('notifications', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addColumn('content_group', 'text', [
                'null' => true,
                'default' => null,
                'limit' => MysqlAdapter::TEXT_LONG,
                'collation' => 'utf8mb4_unicode_520_ci',
                'encoding' => 'utf8mb4',
                'after' => 'content',
            ])
            ->changeColumn('read', 'integer', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'content_group',
            ])
            ->changeColumn('created_at', 'datetime', [
                'null' => false,
                'after' => 'read',
            ])
            ->changeColumn('updated_at', 'datetime', [
                'null' => true,
                'default' => null,
                'after' => 'created_at',
            ])
            ->changeColumn('is_deleted', 'integer', [
                'null' => false,
                'default' => '0',
                'limit' => '3',
                'after' => 'updated_at',
            ])
            ->removeColumn('group')
            ->save();
    }
}
