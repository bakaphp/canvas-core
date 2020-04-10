<?php

use Phinx\Db\Adapter\MysqlAdapter;

class AddCustomFieldsValuesEntityId extends Phinx\Migration\AbstractMigration
{
    public function change()
    {
        $this->table('custom_fields_modules', [
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
            ->addIndex(['name'], [
                'name' => 'name',
                'unique' => false,
            ])
            ->addIndex(['model_name'], [
                'name' => 'model_name',
                'unique' => false,
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
        $this->table('custom_fields', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addIndex(['companies_id', 'apps_id', 'label', 'custom_fields_modules_id', 'is_deleted'], [
                'name' => 'companies_id_apps_id_label_custom_fields_modules_id_is_deleted',
                'unique' => false,
            ])
            ->addIndex(['custom_fields_modules_id'], [
                'name' => 'custom_fields_modules_id',
                'unique' => false,
            ])
            ->addIndex(['label'], [
                'name' => 'label',
                'unique' => false,
            ])
            ->addIndex(['name'], [
                'name' => 'name',
                'unique' => false,
            ])
            ->save();

            $this->table('custom_fields_values', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->changeColumn('label', 'string', [
                'null' => false,
                'limit' => 64,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'custom_fields_id',
            ])
            ->changeColumn('value', 'text', [
                'null' => false,
                'limit' => MysqlAdapter::TEXT_LONG,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'label',
            ])
            ->changeColumn('is_default', 'integer', [
                'null' => false,
                'default' => '0',
                'limit' => '3',
                'after' => 'value',
            ])
            ->changeColumn('created_at', 'datetime', [
                'null' => false,
                'after' => 'is_default',
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
            ->addIndex(['custom_fields_id'], [
                'name' => 'custom_fields_id_entity_id_custom_fields_modules_id',
                'unique' => false,
            ])
            ->addIndex(['custom_fields_id', 'is_default'], [
                'name' => 'custom_fields_id_entity_id_custom_fields_modules_id_is_default',
                'unique' => false,
            ])
            ->save();
    }
}
