<?php

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

class AddCustomFieldsAttributes extends AbstractMigration
{
    public function change()
    {
        $this->table('audits', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_general_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addColumn('id', 'biginteger', [
                'null' => false,
                'limit' => MysqlAdapter::INT_BIG,
                'identity' => 'enable',
                'precision' => '20',
            ])
            ->addColumn('entity_id', 'char', [
                'null' => false,
                'limit' => '36',
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'id',
            ])
            ->addColumn('model_name', 'string', [
                'null' => false,
                'limit' => 64,
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'entity_id',
            ])
            ->addColumn('users_id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'after' => 'model_name',
            ])
            ->addColumn('ip', 'string', [
                'null' => false,
                'limit' => 15,
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'users_id',
            ])
            ->addColumn('type', 'char', [
                'null' => false,
                'limit' => 1,
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'ip',
            ])
            ->addColumn('created_at', 'datetime', [
                'null' => false,
                'after' => 'type',
            ])
        ->addIndex(['entity_id'], [
            'name' => 'idx1',
            'unique' => false,
        ])
        ->addIndex(['model_name'], [
            'name' => 'idx2',
            'unique' => false,
        ])
        ->addIndex(['users_id'], [
            'name' => 'idx3',
            'unique' => false,
        ])
        ->addIndex(['type'], [
            'name' => 'idx4',
            'unique' => false,
        ])
        ->addIndex(['model_name', 'type'], [
            'name' => 'idx5',
            'unique' => false,
        ])
        ->addIndex(['entity_id', 'model_name', 'type'], [
            'name' => 'idx6',
            'unique' => false,
        ])
            ->create();

        $this->table('audits_details', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_general_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addColumn('id', 'biginteger', [
                'null' => false,
                'limit' => MysqlAdapter::INT_BIG,
                'signed' => false,
                'identity' => 'enable',
            ])
            ->addColumn('audits_id', 'biginteger', [
                'null' => false,
                'limit' => MysqlAdapter::INT_BIG,
                'signed' => false,
                'after' => 'id',
            ])
            ->addColumn('field_name', 'string', [
                'null' => false,
                'limit' => 32,
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'audits_id',
            ])
            ->addColumn('old_value', 'text', [
                'null' => true,
                'limit' => 65535,
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'field_name',
            ])
            ->addColumn('old_value_text', 'text', [
                'null' => true,
                'limit' => 65535,
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'old_value',
            ])
            ->addColumn('new_value', 'text', [
                'null' => true,
                'limit' => 65535,
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'old_value_text',
            ])
            ->addColumn('new_value_text', 'text', [
                'null' => true,
                'limit' => 65535,
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'new_value',
            ])
        ->addIndex(['audits_id'], [
            'name' => 'idx1',
            'unique' => false,
        ])
        ->addIndex(['field_name'], [
            'name' => 'field_name',
            'unique' => false,
        ])
            ->create();

        $this->table('custom_fields_modules', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
        ->changeColumn('id', 'integer', [
            'null' => false,
            'limit' => MysqlAdapter::INT_REGULAR,
            'identity' => 'enable',
        ])
        ->changeColumn('apps_id', 'integer', [
            'null' => false,
            'limit' => MysqlAdapter::INT_REGULAR,
            'after' => 'id',
        ])
        ->changeColumn('name', 'string', [
            'null' => false,
            'limit' => 64,
            'collation' => 'utf8mb4_unicode_ci',
            'encoding' => 'utf8mb4',
            'after' => 'apps_id',
        ])
            ->changeColumn('model_name', 'string', [
                'null' => false,
                'limit' => 64,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'name',
            ])
        ->changeColumn('created_at', 'datetime', [
            'null' => false,
            'after' => 'model_name',
        ])
        ->changeColumn('updated_at', 'datetime', [
            'null' => true,
            'after' => 'created_at',
        ])
        ->changeColumn('is_deleted', 'integer', [
            'null' => false,
            'default' => '0',
            'limit' => '3',
            'after' => 'updated_at',
        ])
            ->save();

        $this->table('custom_fields', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
        ->changeColumn('id', 'integer', [
            'null' => false,
            'limit' => MysqlAdapter::INT_REGULAR,
            'identity' => 'enable',
        ])
        ->changeColumn('users_id', 'integer', [
            'null' => false,
            'limit' => MysqlAdapter::INT_REGULAR,
            'after' => 'id',
        ])
        ->changeColumn('companies_id', 'integer', [
            'null' => false,
            'limit' => MysqlAdapter::INT_REGULAR,
            'after' => 'users_id',
        ])
        ->changeColumn('apps_id', 'integer', [
            'null' => false,
            'limit' => MysqlAdapter::INT_REGULAR,
            'after' => 'companies_id',
        ])
        ->changeColumn('name', 'string', [
            'null' => false,
            'limit' => 64,
            'collation' => 'utf8mb4_unicode_ci',
            'encoding' => 'utf8mb4',
            'after' => 'apps_id',
        ])
            ->changeColumn('label', 'string', [
                'null' => true,
                'limit' => 64,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'name',
            ])
        ->changeColumn('custom_fields_modules_id', 'integer', [
            'null' => false,
            'limit' => MysqlAdapter::INT_REGULAR,
            'after' => 'label',
        ])
        ->changeColumn('fields_type_id', 'integer', [
            'null' => false,
            'limit' => MysqlAdapter::INT_REGULAR,
            'after' => 'custom_fields_modules_id',
        ])
            ->addColumn('attributes', 'text', [
                'null' => true,
                'limit' => MysqlAdapter::TEXT_LONG,
                'collation' => 'utf8mb4_bin',
                'encoding' => 'utf8mb4',
                'after' => 'fields_type_id',
            ])
        ->changeColumn('created_at', 'datetime', [
            'null' => false,
            'after' => 'attributes',
        ])
        ->changeColumn('updated_at', 'datetime', [
            'null' => true,
            'after' => 'created_at',
        ])
        ->changeColumn('is_deleted', 'integer', [
            'null' => false,
            'default' => '0',
            'limit' => '3',
            'after' => 'updated_at',
        ])
            ->save();
    }
}
