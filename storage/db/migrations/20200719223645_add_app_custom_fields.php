<?php

use Phinx\Db\Adapter\MysqlAdapter;

class AddAppCustomFields extends Phinx\Migration\AbstractMigration
{
    public function change()
    {
        $this->table('apps_keys', [
            'id' => false,
            'primary_key' => ['apps_id', 'users_id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addIndex(['client_secret_id'], [
                'name' => 'client_secret_id',
                'unique' => false,
            ])
            ->addIndex(['client_id'], [
                'name' => 'client_id',
                'unique' => false,
            ])
            ->addIndex(['last_used_date'], [
                'name' => 'last_used_date',
                'unique' => false,
            ])
            ->addIndex(['client_id', 'apps_id'], [
                'name' => 'client_id_apps_id',
                'unique' => false,
            ])
            ->addIndex(['client_secret_id', 'apps_id'], [
                'name' => 'client_secret_id_apps_id',
                'unique' => false,
            ])
            ->save();
        $this->table('custom_fields_modules', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])

            ->addIndex(['apps_id', 'name', 'model_name'], [
                'name' => 'apps_id_name_model_name',
                'unique' => false,
            ])
            ->addIndex(['apps_id', 'name', 'model_name', 'is_deleted'], [
                'name' => 'apps_id_name_model_name_is_deleted',
                'unique' => false,
            ])
            ->addIndex(['apps_id', 'model_name', 'is_deleted'], [
                'name' => 'apps_id_model_name_is_deleted',
                'unique' => false,
            ])
            ->save();
        $this->table('countries', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addColumn('code', 'string', [
                'null' => false,
                'limit' => 128,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'name',
            ])
            ->changeColumn('flag', 'string', [
                'null' => true,
                'default' => null,
                'limit' => 128,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'code',
            ])
            ->changeColumn('created_at', 'datetime', [
                'null' => false,
                'after' => 'flag',
            ])
            ->changeColumn('updated_at', 'datetime', [
                'null' => true,
                'default' => null,
                'after' => 'created_at',
            ])
            ->changeColumn('is_deleted', 'integer', [
                'null' => false,
                'default' => '0',
                'limit' => '3',
                'after' => 'updated_at',
            ])
            ->save();
        $this->table('apps_custom_fields', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addColumn('id', 'biginteger', [
                'null' => false,
                'limit' => MysqlAdapter::INT_BIG,
                'identity' => 'enable',
            ])
            ->addColumn('companies_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'id',
            ])
            ->addColumn('users_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'companies_id',
            ])
            ->addColumn('model_name', 'string', [
                'null' => false,
                'limit' => 255,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'users_id',
            ])
            ->addColumn('entity_id', 'biginteger', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_BIG,
                'after' => 'model_name',
            ])
            ->addColumn('name', 'string', [
                'null' => false,
                'limit' => 255,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'entity_id',
            ])
            ->addColumn('label', 'string', [
                'null' => false,
                'limit' => 255,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'name',
            ])
            ->addColumn('value', 'text', [
                'null' => true,
                'default' => null,
                'limit' => MysqlAdapter::TEXT_LONG,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'label',
            ])
            ->addColumn('created_at', 'datetime', [
                'null' => false,
                'after' => 'value',
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
            ->addIndex(['companies_id'], [
                'name' => 'companies_id',
                'unique' => false,
            ])
            ->addIndex(['users_id'], [
                'name' => 'users_id',
                'unique' => false,
            ])
            ->addIndex(['model_name'], [
                'name' => 'model_name',
                'unique' => false,
            ])
            ->addIndex(['entity_id'], [
                'name' => 'entity_id',
                'unique' => false,
            ])
            ->addIndex(['name'], [
                'name' => 'name',
                'unique' => false,
            ])
            ->addIndex(['label'], [
                'name' => 'label',
                'unique' => false,
            ])
            ->addIndex(['companies_id', 'model_name', 'entity_id'], [
                'name' => 'companies_id_model_name_entity_id',
                'unique' => false,
            ])
            ->addIndex(['model_name', 'entity_id'], [
                'name' => 'model_name_2',
                'unique' => false,
            ])
            ->addIndex(['model_name', 'entity_id', 'name'], [
                'name' => 'model_name_3',
                'unique' => false,
            ])
            ->addIndex(['created_at'], [
                'name' => 'created_at',
                'unique' => false,
            ])
            ->addIndex(['updated_at'], [
                'name' => 'updated_at',
                'unique' => false,
            ])
            ->addIndex(['is_deleted'], [
                'name' => 'is_deleted',
                'unique' => false,
            ])
            ->addIndex(['companies_id', 'model_name', 'entity_id', 'name'], [
                'name' => 'companies_id_model_name_entity_id_name',
                'unique' => false,
            ])
            ->create();

        $this->table('custom_fields_values', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addIndex(['custom_fields_id'], [
                'name' => 'custom_fields_id_entity_id_custom_fields_modules_id',
                'unique' => false,
            ])
            ->addIndex(['custom_fields_id', 'is_default'], [
                'name' => 'custom_fields_id_entity_id_custom_fields_modules_id_is_default',
                'unique' => false,
            ])
            ->save();

        $this->table('resources', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addIndex(['apps_id'], [
                'name' => 'apps_id',
                'unique' => false,
            ])
            ->save();

        $this->table('user_roles', [
            'id' => false,
            'primary_key' => ['users_id', 'apps_id', 'companies_id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addIndex(['users_id', 'apps_id', 'companies_id'], [
                'name' => 'user_roles_UN',
                'unique' => true,
            ])
            ->save();

        $this->table('payment_methods', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addIndex(['is_default'], [
                'name' => 'is_default',
                'unique' => false,
            ])
            ->save();
    }
}
