<?php


class AddNotificationImportanceIndex extends Phinx\Migration\AbstractMigration
{
    public function change()
    {
        $this->table('users_notification_entity_importance', [
            'id' => false,
            'primary_key' => ['apps_id', 'users_id', 'entity_id', 'system_modules_id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8',
            'collation' => 'utf8_general_mysql500_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addIndex(['apps_id', 'users_id', 'entity_id', 'is_deleted'], [
                'name' => 'apps_id_users_id_entity_id_is_deleted',
                'unique' => false,
            ])
            ->addIndex(['apps_id', 'users_id', 'entity_id', 'system_modules_id', 'is_deleted'], [
                'name' => 'apps_id_users_id_entity_system_module',
                'unique' => false,
            ])
            ->save();
    }
}
