<?php

use Phinx\Db\Adapter\MysqlAdapter;

class AddNotificationTypeChannel extends Phinx\Migration\AbstractMigration
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
            ->addColumn('notification_channel_id', 'boolean', [
                'null' => true,
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'parent_id',
            ])
            ->save();
    }
}
