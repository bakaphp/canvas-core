<?php

use Phinx\Db\Adapter\MysqlAdapter;

class AddBranchToNotifications extends Phinx\Migration\AbstractMigration
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
            ->addColumn('companies_branches_id', 'integer', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'companies_id',
            ])
            ->addIndex(['companies_branches_id'], [
                'name' => 'companies_branches_id',
                'unique' => false,
            ])
            ->addIndex(['users_id', 'companies_id', 'apps_id', 'is_deleted'], [
                'name' => 'users_id_companies_id_apps_id_is_deleted',
                'unique' => false,
            ])
            ->addIndex(['users_id', 'companies_id', 'companies_branches_id', 'apps_id', 'is_deleted'], [
                'name' => 'users_id_companies_id_companies_branches_id_apps_id_is_deleted',
                'unique' => false,
            ])
            ->addIndex(['users_id', 'companies_id', 'apps_id', 'notification_type_id', 'is_deleted'], [
                'name' => 'users_id_companies_id_apps_id_notification_type_id_is_deleted',
                'unique' => false,
            ])
            ->save();
    }
}
