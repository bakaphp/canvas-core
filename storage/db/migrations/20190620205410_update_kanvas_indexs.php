<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class UpdateKanvasIndexs extends AbstractMigration
{
    public function change()
    {
        $this->table('custom_fields', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
        ->addIndex(['companies_id', 'apps_id'], [
            'name' => 'companies_id_apps_id',
            'unique' => false,
        ])
        ->addIndex(['users_id'], [
            'name' => 'users_id',
            'unique' => false,
        ])
        ->addIndex(['fields_type_id'], [
            'name' => 'fields_type_id',
            'unique' => false,
        ])
            ->save();
        $this->table('filesystem', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
        ->addIndex(['companies_id', 'is_deleted'], [
            'name' => 'companies_id_is_deleted',
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
        ->addIndex(['filesystem_id', 'entity_id', 'system_modules_id', 'field_name'], [
            'name' => 'filesystem_id_entity_id_system_modules_id_field_name',
            'unique' => false,
        ])
        ->addIndex(['filesystem_id', 'entity_id', 'system_modules_id', 'field_name', 'is_deleted'], [
            'name' => 'filesystem_id_entity_id_system_modules_id_field_name_is_deleted',
            'unique' => false,
        ])
            ->save();
        $this->table('languages', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
        ->addIndex(['order'], [
            'name' => 'order',
            'unique' => false,
        ])
            ->save();
        $this->table('companies_branches', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
        ->addIndex(['companies_id'], [
            'name' => 'companies_id',
            'unique' => false,
        ])
        ->addIndex(['is_default'], [
            'name' => 'is_default',
            'unique' => false,
        ])
            ->save();
        $this->table('companies_custom_fields', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
        ->addIndex(['companies_id', 'custom_fields_id'], [
            'name' => 'companies_id_custom_fields_id',
            'unique' => false,
        ])
            ->save();
        $this->table('users', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'COMPACT',
        ])
        ->addIndex(['roles_id'], [
            'name' => 'roles_id',
            'unique' => false,
        ])
        ->addIndex(['default_company'], [
            'name' => 'default_company',
            'unique' => false,
        ])
        ->addIndex(['default_company_branch'], [
            'name' => 'default_company_branch',
            'unique' => false,
        ])
        ->addIndex(['email'], [
            'name' => 'email',
            'unique' => false,
        ])
            ->save();
        $this->table('apps_plans', [
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
        ->addIndex(['stripe_id'], [
            'name' => 'stripe_id',
            'unique' => false,
        ])
        ->addIndex(['currency_id'], [
            'name' => 'currency_id',
            'unique' => false,
        ])
        ->addIndex(['is_default'], [
            'name' => 'is_default',
            'unique' => false,
        ])
            ->save();
    }
}
