<?php

use Phinx\Db\Adapter\MysqlAdapter;

class AddStripeSubscriptionStatus extends Phinx\Migration\AbstractMigration
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
            ->changeColumn('name', 'string', [
                'null' => false,
                'limit' => 250,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'apps_id',
            ])
            ->changeColumn('stripe_id', 'string', [
                'null' => false,
                'limit' => 250,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'name',
            ])
            ->changeColumn('stripe_plan', 'string', [
                'null' => false,
                'limit' => 250,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'stripe_id',
            ])
            ->addColumn('stripe_status', 'string', [
                'null' => false,
                'limit' => 25,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'stripe_plan',
            ])
            ->removeColumn('apps_plans_id')
            ->addIndex(['stripe_status'], [
                'name' => 'stripe_status',
                'unique' => false,
            ])
            ->removeIndexByName('apps_plans_id')
            ->save();
        $this->table('subscription_items', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addColumn('apps_plans_id', 'integer', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'subscription_id',
            ])
            ->changeColumn('stripe_id', 'string', [
                'null' => false,
                'limit' => 255,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'apps_plans_id',
            ])
            ->changeColumn('stripe_plan', 'string', [
                'null' => false,
                'limit' => 255,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'stripe_id',
            ])
            ->changeColumn('quantity', 'integer', [
                'null' => false,
                'default' => '1',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'stripe_plan',
            ])
            ->changeColumn('created_at', 'datetime', [
                'null' => false,
                'after' => 'quantity',
            ])
            ->changeColumn('updated_at', 'datetime', [
                'null' => false,
                'after' => 'created_at',
            ])
            ->changeColumn('is_deleted', 'integer', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'updated_at',
            ])
            ->addIndex(['apps_plans_id'], [
                'name' => 'apps_plans_id',
                'unique' => false,
            ])
            ->save();
    }
}
