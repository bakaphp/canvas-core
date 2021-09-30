<?php


class AddNotificationSettingsIndex extends Phinx\Migration\AbstractMigration
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
            ->addIndex(['users_id', 'apps_id', 'notifications_types_id', 'is_deleted'], [
                'name' => 'users_id_apps_id_notifications_types_id_is_deleted',
                'unique' => false,
            ])
            ->save();
    }
}
