<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class AddComplexFilters extends AbstractMigration
{
    public function change()
    {
        $this->table('custom_filters', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'precision' => '10',
                'signed' => false,
            ])
            ->addColumn('system_modules_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'precision' => '10',
                'after' => 'id',
            ])
            ->addColumn('apps_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'precision' => '10',
                'after' => 'system_modules_id',
            ])
            ->addColumn('companies_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'precision' => '10',
                'after' => 'apps_id',
            ])
            ->addColumn('companies_branch_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'precision' => '10',
                'after' => 'companies_id',
            ])
            ->addColumn('users_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'precision' => '10',
                'after' => 'companies_branch_id',
            ])
            ->addColumn('name', 'string', [
                'null' => false,
                'limit' => 100,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'users_id',
            ])
            ->addColumn('sequence_logic', 'text', [
                'null' => false,
                'limit' => 65535,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'name',
            ])
            ->addColumn('total_conditions', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'precision' => '10',
                'after' => 'sequence_logic',
            ])
            ->addColumn('description', 'text', [
                'null' => true,
                'limit' => 65535,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'total_conditions',
            ])
            ->addColumn('created_at', 'datetime', [
                'null' => false,
                'after' => 'description',
            ])
            ->addColumn('updated_at', 'datetime', [
                'null' => true,
                'after' => 'created_at',
            ])
            ->addColumn('is_deleted', 'integer', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'precision' => '3',
                'after' => 'updated_at',
            ])
        ->addIndex(['id'], [
            'name' => 'id',
            'unique' => false,
        ])
            ->create();

        $this->table('custom_filters_conditions', [
            'id' => false,
            'primary_key' => ['custom_filter_id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addColumn('custom_filter_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'precision' => '10',
                'signed' => false,
            ])
            ->addColumn('position', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'precision' => '10',
                'after' => 'custom_filter_id',
            ])
            ->addColumn('conditional', 'string', [
                'null' => false,
                'limit' => 5,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'position',
            ])
            ->addColumn('value', 'string', [
                'null' => false,
                'limit' => 255,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'conditional',
            ])
            ->addColumn('created_at', 'datetime', [
                'null' => false,
                'after' => 'value',
            ])
            ->addColumn('updated_at', 'datetime', [
                'null' => true,
                'after' => 'created_at',
            ])
            ->addColumn('is_deleted', 'integer', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'precision' => '3',
                'after' => 'updated_at',
            ])
        ->addIndex(['custom_filter_id'], [
            'name' => 'custom_filter_id',
            'unique' => false,
        ])
            ->create();
    }
}
