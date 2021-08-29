<?php

use Phinx\Db\Adapter\MysqlAdapter;

class RenameNotificationRelevancy extends Phinx\Migration\AbstractMigration
{
    public function change()
    {
        $this->table('notifications_importance', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8',
            'collation' => 'utf8_general_mysql500_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('apps_id', 'integer', [
                'null' => true,
                'default' => null,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'id',
            ])
            ->addColumn('name', 'string', [
                'null' => false,
                'limit' => 255,
                'collation' => 'utf8_general_mysql500_ci',
                'encoding' => 'utf8',
                'after' => 'apps_id',
            ])
            ->addColumn('validation_expression', 'text', [
                'null' => false,
                'limit' => 65535,
                'collation' => 'utf8_general_mysql500_ci',
                'encoding' => 'utf8',
                'after' => 'name',
            ])
            ->addColumn('created_at', 'datetime', [
                'null' => false,
                'after' => 'validation_expression',
            ])
            ->addColumn('updated_at', 'datetime', [
                'null' => true,
                'default' => null,
                'after' => 'created_at',
            ])
            ->addColumn('is_deleted', 'integer', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'updated_at',
            ])
            ->addIndex(['apps_id'], [
                'name' => 'apps_id',
                'unique' => false,
            ])
            ->addIndex(['created_at'], [
                'name' => 'created_at',
                'unique' => false,
            ])
            ->addIndex(['is_deleted'], [
                'name' => 'is_deleted',
                'unique' => false,
            ])
            ->create();
        $this->table('users_notification_entity_importance', [
            'id' => false,
            'primary_key' => ['apps_id', 'users_id', 'entity_id', 'system_modules_id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8',
            'collation' => 'utf8_general_mysql500_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addColumn('apps_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
            ])
            ->addColumn('users_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'apps_id',
            ])
            ->addColumn('entity_id', 'char', [
                'null' => false,
                'limit' => 50,
                'collation' => 'utf8_general_mysql500_ci',
                'encoding' => 'utf8',
                'after' => 'users_id',
            ])
            ->addColumn('system_modules_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'entity_id',
            ])
            ->addColumn('importance_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'system_modules_id',
            ])
            ->addColumn('created_at', 'datetime', [
                'null' => false,
                'after' => 'importance_id',
            ])
            ->addColumn('updated_at', 'datetime', [
                'null' => true,
                'default' => null,
                'after' => 'created_at',
            ])
            ->addColumn('is_deleted', 'integer', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'updated_at',
            ])
            ->addIndex(['created_at'], [
                'name' => 'created_at',
                'unique' => false,
            ])
            ->addIndex(['is_deleted'], [
                'name' => 'is_deleted',
                'unique' => false,
            ])
            ->addIndex(['importance_id'], [
                'name' => 'relevancies_id',
                'unique' => false,
            ])
            ->create();
        $this->table('users_notification_entity_relevancies')->drop()->save();
        $this->table('notifications_relevancies')->drop()->save();
    }
}
