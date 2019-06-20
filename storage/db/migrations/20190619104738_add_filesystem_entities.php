<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class AddFilesystemEntities extends AbstractMigration
{
    public function change()
    {
        $this->table('apps', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'COMPACT',
        ])
        ->addIndex(['key'], [
            'name' => 'apps_key',
            'unique' => false,
        ])
            ->save();

        $this->table('filesystem_entities', [
            'id' => false,
            'primary_key' => ['filesystem_id', 'entity_id', 'system_modules_id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addColumn('filesystem_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
            ])
            ->addColumn('entity_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'filesystem_id',
            ])
            ->addColumn('system_modules_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'entity_id',
            ])
            ->addColumn('created_at', 'datetime', [
                'null' => false,
                'after' => 'system_modules_id',
            ])
            ->addColumn('updated_at', 'datetime', [
                'null' => true,
                'after' => 'created_at',
            ])
            ->addColumn('is_deleted', 'integer', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'updated_at',
            ])
        ->addIndex(['filesystem_id', 'entity_id', 'system_modules_id'], [
            'name' => 'uniqueids',
            'unique' => true,
        ])
        ->addIndex(['filesystem_id', 'entity_id', 'system_modules_id'], [
            'name' => 'entitiesid',
            'unique' => false,
        ])
            ->create();
    }
}
