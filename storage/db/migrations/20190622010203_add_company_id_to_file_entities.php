<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class AddCompanyIdToFileEntities extends AbstractMigration
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
        ->addIndex(['id', 'companies_id', 'is_deleted'], [
            'name' => 'filesystemindex2',
            'unique' => false,
        ])
            ->save();
        $this->table('filesystem_entities', [
            'id' => false,
            'primary_key' => ['filesystem_id', 'entity_id', 'companies_id', 'system_modules_id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addColumn('companies_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'entity_id',
            ])
        ->changeColumn('system_modules_id', 'integer', [
            'null' => false,
            'limit' => MysqlAdapter::INT_REGULAR,
            'after' => 'companies_id',
        ])
        ->changeColumn('field_name', 'string', [
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
        ->addIndex(['companies_id'], [
            'name' => 'companies_id',
            'unique' => false,
        ])
        ->addIndex(['filesystem_id', 'entity_id', 'companies_id', 'system_modules_id', 'field_name'], [
            'name' => 'filesystem_attachment',
            'unique' => false,
        ])
        ->addIndex(['filesystem_id', 'entity_id', 'companies_id', 'system_modules_id', 'field_name', 'is_deleted'], [
            'name' => 'filesystem_attachmenidex2',
            'unique' => false,
        ])
            ->removeIndexByName('filesystem_id_entity_id_system_modules_id_field_name')
            ->removeIndexByName('filesystem_id_entity_id_system_modules_id_field_name_is_deleted')
            ->save();
    }
}
