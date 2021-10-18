<?php

use Phinx\Db\Adapter\MysqlAdapter;

class UpdateNotificationParentId extends Phinx\Migration\AbstractMigration
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
            ->addIndex(['is_deleted'], [
                'name' => 'is_deleted',
                'unique' => false,
            ])
            ->addIndex(['created_at'], [
                'name' => 'created_at',
                'unique' => false,
            ])
            ->addIndex(['read'], [
                'name' => 'read',
                'unique' => false,
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
            ->addColumn('parent_id', 'integer', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'system_modules_id',
            ])
            ->removeColumn('parents_id')
            ->removeIndexByName('parents_id')
            ->addIndex(['parent_id'], [
                'name' => 'parents_id',
                'unique' => false,
            ])
            ->removeIndexByName('apps_id_system_modules_id_parents_id')
            ->addIndex(['apps_id', 'system_modules_id', 'parent_id'], [
                'name' => 'apps_id_system_modules_id_parents_id',
                'unique' => false,
            ])
            ->save();
    }
}
