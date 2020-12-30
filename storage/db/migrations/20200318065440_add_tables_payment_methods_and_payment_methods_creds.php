<?php

use Phinx\Db\Adapter\MysqlAdapter;

class AddTablesPaymentMethodsAndPaymentMethodsCreds extends Phinx\Migration\AbstractMigration
{
    public function change()
    {
        $this->table('payment_methods', [
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
                'identity' => 'enable',
            ])
            ->addColumn('name', 'string', [
                'null' => false,
                'limit' => 64,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'id',
            ])
            ->addColumn('is_default', 'integer', [
                'null' => true,
                'default' => '0',
                'limit' => '1',
                'after' => 'name',
            ])
            ->addColumn('created_at', 'datetime', [
                'null' => true,
                'after' => 'is_default',
            ])
            ->addColumn('updated_at', 'datetime', [
                'null' => true,
                'after' => 'created_at',
            ])
            ->addColumn('is_deleted', 'integer', [
                'null' => true,
                'default' => '0',
                'limit' => '1',
                'after' => 'updated_at',
            ])
            ->create();
        $this->table('payment_methods_creds', [
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
                'identity' => 'enable',
            ])
            ->addColumn('users_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'id',
            ])
            ->addColumn('companies_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'users_id',
            ])
            ->addColumn('apps_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'companies_id',
            ])
            ->addColumn('payment_methods_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'apps_id',
            ])
            ->addColumn('payment_ending_numbers', 'string', [
                'null' => false,
                'limit' => 8,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'payment_methods_id',
            ])
            ->addColumn('expiration_date', 'date', [
                'null' => false,
                'after' => 'payment_ending_numbers',
            ])
            ->addColumn('zip_code', 'string', [
                'null' => true,
                'limit' => 12,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'expiraton_date',
            ])
            ->addColumn('created_at', 'datetime', [
                'null' => true,
                'after' => 'zip_code',
            ])
            ->addColumn('updated_at', 'datetime', [
                'null' => true,
                'after' => 'created_at',
            ])
            ->addColumn('is_deleted', 'integer', [
                'null' => true,
                'default' => '0',
                'limit' => '1',
                'after' => 'updated_at',
            ])
            ->create();
    }
}
