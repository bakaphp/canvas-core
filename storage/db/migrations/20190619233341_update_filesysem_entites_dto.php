<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class UpdateFilesysemEntitesDto extends AbstractMigration
{
    public function change()
    {
        $this->table('filesystem', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
        ->changeColumn('name', 'string', [
            'null' => false,
            'limit' => 255,
            'collation' => 'utf8mb4_unicode_ci',
            'encoding' => 'utf8mb4',
            'after' => 'users_id',
        ])
        ->changeColumn('path', 'string', [
            'null' => false,
            'limit' => 255,
            'collation' => 'utf8mb4_unicode_ci',
            'encoding' => 'utf8mb4',
            'after' => 'name',
        ])
        ->changeColumn('url', 'string', [
            'null' => false,
            'limit' => 255,
            'collation' => 'utf8mb4_unicode_ci',
            'encoding' => 'utf8mb4',
            'after' => 'path',
        ])
        ->changeColumn('size', 'string', [
            'null' => false,
            'limit' => 255,
            'collation' => 'utf8mb4_unicode_ci',
            'encoding' => 'utf8mb4',
            'after' => 'url',
        ])
        ->changeColumn('file_type', 'string', [
            'null' => false,
            'limit' => 16,
            'collation' => 'utf8mb4_unicode_ci',
            'encoding' => 'utf8mb4',
            'after' => 'size',
        ])
        ->changeColumn('created_at', 'datetime', [
            'null' => false,
            'after' => 'file_type',
        ])
        ->changeColumn('updated_at', 'datetime', [
            'null' => true,
            'after' => 'created_at',
        ])
            ->removeColumn('system_modules_id')
            ->removeColumn('entity_id')
        ->addIndex(['companies_id'], [
            'name' => 'companies_id',
            'unique' => false,
        ])
        ->addIndex(['apps_id'], [
            'name' => 'apps_id',
            'unique' => false,
        ])
        ->addIndex(['file_type'], [
            'name' => 'file_type',
            'unique' => false,
        ])
        ->addIndex(['id', 'file_type', 'is_deleted'], [
            'name' => 'id_file_type_is_deleted',
            'unique' => false,
        ])
        ->addIndex(['id', 'is_deleted'], [
            'name' => 'id_is_deleted',
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
            ->addColumn('field_name', 'string', [
                'null' => true,
                'default' => 'NULL',
                'limit' => 50,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'system_modules_id',
            ])
        ->changeColumn('created_at', 'datetime', [
            'null' => false,
            'after' => 'field_name',
        ])
        ->changeColumn('updated_at', 'datetime', [
            'null' => true,
            'after' => 'created_at',
        ])
        ->changeColumn('is_deleted', 'boolean', [
            'null' => false,
            'default' => '0',
            'limit' => MysqlAdapter::INT_TINY,
            'after' => 'updated_at',
        ])
        ->addIndex(['filesystem_id'], [
            'name' => 'filesystem_id',
            'unique' => false,
        ])
            ->removeIndexByName('uniqueids')
            ->save();
    }
}
