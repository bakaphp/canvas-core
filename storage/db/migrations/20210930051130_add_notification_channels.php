<?php

use Phinx\Db\Adapter\MysqlAdapter;

class AddNotificationChannels extends Phinx\Migration\AbstractMigration
{
    public function change()
    {
        $this->table('notifications_channels', [
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
            ->addColumn('name', 'string', [
                'null' => false,
                'limit' => 255,
                'collation' => 'utf8_general_mysql500_ci',
                'encoding' => 'utf8',
                'after' => 'id',
            ])
            ->addColumn('slug', 'string', [
                'null' => false,
                'limit' => 255,
                'collation' => 'utf8_general_mysql500_ci',
                'encoding' => 'utf8',
                'after' => 'name',
            ])
            ->addColumn('created_at', 'datetime', [
                'null' => false,
                'after' => 'slug',
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
            ->create();
    }
}
