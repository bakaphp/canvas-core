<?php

use Phinx\Db\Adapter\MysqlAdapter;

class UpdateCashierSubscriptionModel extends Phinx\Migration\AbstractMigration
{
    public function change()
    {
        $this->table('subscriptions', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addColumn('companies_groups_id', 'integer', [
                'null' => false,
                'limit' => '10',
                'after' => 'user_id',
            ])
            ->removeColumn('companies_id')
            ->removeIndexByName('companies_id')
            ->addIndex(['companies_groups_id'], [
                'name' => 'companies_id',
                'unique' => false,
            ])
            ->save();

        $this->table('companies', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '					',
            'row_format' => 'COMPACT',
        ])
            ->changeColumn('phone', 'string', [
                'null' => true,
                'default' => null,
                'limit' => 64,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'timezone',
            ])
            ->changeColumn('users_id', 'integer', [
                'null' => false,
                'limit' => '10',
                'after' => 'phone',
            ])
            ->changeColumn('has_activities', 'boolean', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'users_id',
            ])
            ->changeColumn('currency_id', 'integer', [
                'null' => true,
                'default' => null,
                'limit' => '10',
                'after' => 'has_activities',
            ])
            ->changeColumn('system_modules_id', 'integer', [
                'null' => true,
                'default' => null,
                'limit' => '10',
                'after' => 'currency_id',
            ])
            ->changeColumn('created_at', 'datetime', [
                'null' => true,
                'default' => null,
                'after' => 'system_modules_id',
            ])
            ->changeColumn('updated_at', 'datetime', [
                'null' => true,
                'default' => null,
                'after' => 'created_at',
            ])
            ->changeColumn('is_deleted', 'integer', [
                'null' => true,
                'default' => null,
                'limit' => '10',
                'after' => 'updated_at',
            ])
            ->removeColumn('companies_group_id')
            ->save();

        $this->table('user_company_apps', [
            'id' => false,
            'primary_key' => ['companies_id', 'apps_id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'COMPACT',
        ])
            ->changeColumn('created_at', 'datetime', [
                'null' => false,
                'after' => 'apps_id',
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
            ->removeColumn('stripe_id')
            ->removeColumn('subscriptions_id')
            ->addIndex(['created_at'], [
                'name' => 'created_at',
                'unique' => false,
            ])
            ->addIndex(['is_deleted'], [
                'name' => 'is_deleted',
                'unique' => false,
            ])
            ->removeIndexByName('stripe_id')
            ->removeIndexByName('subscriptions_id')
            ->save();

        $this->table('users', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->changeColumn('card_last_four', 'string', [
                'null' => true,
                'default' => null,
                'limit' => 255,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'system_modules_id',
            ])
            ->changeColumn('card_brand', 'string', [
                'null' => true,
                'default' => null,
                'limit' => 255,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'card_last_four',
            ])
            ->changeColumn('trial_ends_at', 'timestamp', [
                'null' => true,
                'default' => null,
                'after' => 'card_brand',
            ])
            ->changeColumn('created_at', 'datetime', [
                'null' => true,
                'default' => null,
                'after' => 'trial_ends_at',
            ])
            ->changeColumn('updated_at', 'datetime', [
                'null' => true,
                'default' => null,
                'after' => 'created_at',
            ])
            ->changeColumn('is_deleted', 'integer', [
                'null' => false,
                'default' => '0',
                'limit' => '10',
                'after' => 'updated_at',
            ])
            ->changeColumn('status', 'integer', [
                'null' => false,
                'default' => '1',
                'limit' => '3',
                'after' => 'is_deleted',
            ])
            ->removeColumn('stripe_id')
            ->save();

        $this->table('companies_associations', [
            'id' => false,
            'primary_key' => ['companies_groups_id', 'companies_id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addColumn('is_default', 'boolean', [
                'null' => true,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'companies_id',
            ])
            ->changeColumn('created_at', 'datetime', [
                'null' => true,
                'default' => null,
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
                'limit' => '1',
                'after' => 'updated_at',
            ])
            ->addIndex(['companies_groups_id', 'companies_id', 'is_default'], [
                'name' => 'companies_groups_id_companies_id_is_default',
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
            ->changeColumn('payment_frequencies_id', 'integer', [
                'null' => true,
                'default' => '1',
                'limit' => MysqlAdapter::INT_REGULAR,
                'comment' => 'The integers in this field represent months',
                'after' => 'is_default',
            ])
            ->changeColumn('created_at', 'datetime', [
                'null' => false,
                'after' => 'payment_frequencies_id',
            ])
            ->changeColumn('updated_at', 'datetime', [
                'null' => true,
                'default' => null,
                'after' => 'created_at',
            ])
            ->changeColumn('is_deleted', 'integer', [
                'null' => true,
                'default' => '0',
                'limit' => '3',
                'after' => 'updated_at',
            ])
            ->save();

        $this->table('payment_methods_credentials', [
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
            ->addColumn('companies_groups_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'users_id',
            ])
            ->addColumn('apps_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'companies_groups_id',
            ])
            ->addColumn('stripe_card_id', 'string', [
                'null' => true,
                'default' => null,
                'limit' => 255,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'apps_id',
            ])
            ->addColumn('payment_methods_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'stripe_card_id',
            ])
            ->addColumn('payment_ending_numbers', 'string', [
                'null' => false,
                'limit' => 8,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'payment_methods_id',
            ])
            ->addColumn('payment_methods_brand', 'string', [
                'null' => true,
                'default' => null,
                'limit' => 32,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'payment_ending_numbers',
            ])
            ->addColumn('expiration_date', 'string', [
                'null' => false,
                'default' => '',
                'limit' => 50,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'payment_methods_brand',
            ])
            ->addColumn('zip_code', 'string', [
                'null' => true,
                'default' => null,
                'limit' => 12,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'expiration_date',
            ])
            ->addColumn('is_default', 'boolean', [
                'null' => true,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'zip_code',
            ])
            ->addColumn('created_at', 'datetime', [
                'null' => true,
                'default' => null,
                'after' => 'is_default',
            ])
            ->addColumn('updated_at', 'datetime', [
                'null' => true,
                'default' => null,
                'after' => 'created_at',
            ])
            ->addColumn('is_deleted', 'integer', [
                'null' => true,
                'default' => '0',
                'limit' => '1',
                'after' => 'updated_at',
            ])
            ->addIndex(['users_id'], [
                'name' => 'users_id',
                'unique' => false,
            ])
            ->addIndex(['apps_id'], [
                'name' => 'apps_id',
                'unique' => false,
            ])
            ->addIndex(['payment_methods_id'], [
                'name' => 'payment_methods_id',
                'unique' => false,
            ])
            ->addIndex(['payment_ending_numbers'], [
                'name' => 'payment_ending_numbers',
                'unique' => false,
            ])
            ->addIndex(['expiration_date'], [
                'name' => 'expiration_date',
                'unique' => false,
            ])
            ->addIndex(['companies_groups_id'], [
                'name' => 'companies_id',
                'unique' => false,
            ])
            ->addIndex(['stripe_card_id'], [
                'name' => 'stripe_card_id',
                'unique' => false,
            ])
            ->create();
        $this->table('subscription_items', [
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
            ->addColumn('subscription_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'id',
            ])
            ->addColumn('stripe_id', 'string', [
                'null' => false,
                'limit' => 255,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'subscription_id',
            ])
            ->addColumn('stripe_plan', 'string', [
                'null' => false,
                'limit' => 255,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'stripe_id',
            ])
            ->addColumn('quantity', 'integer', [
                'null' => false,
                'default' => '1',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'stripe_plan',
            ])
            ->addColumn('created_at', 'datetime', [
                'null' => false,
                'after' => 'quantity',
            ])
            ->addColumn('updated_at', 'datetime', [
                'null' => false,
                'after' => 'created_at',
            ])
            ->addColumn('is_deleted', 'integer', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'updated_at',
            ])
            ->addIndex(['subscription_id'], [
                'name' => 'subscription_id',
                'unique' => false,
            ])
            ->addIndex(['stripe_id'], [
                'name' => 'stripe_id',
                'unique' => false,
            ])
            ->addIndex(['stripe_plan'], [
                'name' => 'stripe_plan',
                'unique' => false,
            ])
            ->create();
        $this->table('companies_groups', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addColumn('stripe_id', 'string', [
                'null' => true,
                'default' => null,
                'limit' => 255,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'users_id',
            ])
            ->addColumn('is_default', 'integer', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'stripe_id',
            ])
            ->changeColumn('created_at', 'datetime', [
                'null' => true,
                'default' => null,
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
                'limit' => '1',
                'after' => 'updated_at',
            ])
            ->addIndex(['apps_id'], [
                'name' => 'apps_id',
                'unique' => false,
            ])
            ->addIndex(['users_id'], [
                'name' => 'users_id',
                'unique' => false,
            ])
            ->addIndex(['stripe_id'], [
                'name' => 'stripe_id',
                'unique' => false,
            ])
            ->addIndex(['is_deleted'], [
                'name' => 'is_deleted',
                'unique' => false,
            ])
            ->addIndex(['is_default'], [
                'name' => 'is_default',
                'unique' => false,
            ])
            ->save();

        $this->table('payment_methods_creds')->drop()->save();
    }
}
