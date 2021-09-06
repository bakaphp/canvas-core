<?php

use Phinx\Db\Adapter\MysqlAdapter;

class UpdateNotificationSettings extends Phinx\Migration\AbstractMigration
{
    public function change()
    {
        $this->table('users_notification_settings', [
            'id' => false,
            'primary_key' => ['users_id', 'apps_id', 'notifications_types_id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8',
            'collation' => 'utf8_general_mysql500_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->changeColumn('is_enabled', 'integer', [
                'null' => false,
                'default' => '1',
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'notifications_types_id',
            ])
            ->changeColumn('channels', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_520_ci',
                'encoding' => 'utf8mb4',
                'after' => 'is_enabled',
            ])
            ->removeIndexByName('is_enabled')
            ->addIndex(['is_enabled'], [
                'name' => 'is_enabled',
                'unique' => false,
            ])
            ->save();
    }
}
