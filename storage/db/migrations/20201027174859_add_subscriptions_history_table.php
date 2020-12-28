<?php

use Phinx\Db\Adapter\MysqlAdapter;

class AddSubscriptionsHistoryTable extends Phinx\Migration\AbstractMigration
{
    public function change()
    {
        $this->execute("ALTER DATABASE CHARACTER SET 'utf8mb4';");
        $this->execute("ALTER DATABASE COLLATE='utf8mb4_unicode_ci';");
        $this->table('subscriptions_history', [
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
                'limit' => '10',
                'identity' => 'enable',
            ])
            ->addColumn('users_id', 'integer', [
                'null' => false,
                'limit' => '10',
                'after' => 'id',
            ])
            ->addColumn('companies_id', 'integer', [
                'null' => false,
                'limit' => '10',
                'after' => 'user_id',
            ])
            ->addColumn('apps_id', 'integer', [
                'null' => false,
                'limit' => '10',
                'after' => 'companies_id',
            ])
            ->addColumn('apps_plans_id', 'integer', [
                'null' => true,
                'default' => null,
                'limit' => '10',
                'after' => 'apps_id',
            ])
            ->addColumn('name', 'string', [
                'null' => false,
                'limit' => 250,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'apps_plans_id',
            ])
            ->addColumn('stripe_id', 'string', [
                'null' => false,
                'limit' => 250,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'name',
            ])
            ->addColumn('stripe_plan', 'string', [
                'null' => false,
                'limit' => 250,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'stripe_id',
            ])
            ->addColumn('quantity', 'integer', [
                'null' => false,
                'limit' => '10',
                'after' => 'stripe_plan',
            ])
            ->addColumn('trial_ends_at', 'timestamp', [
                'null' => true,
                'default' => null,
                'after' => 'quantity',
            ])
            ->addColumn('grace_period_ends', 'datetime', [
                'null' => true,
                'default' => null,
                'after' => 'trial_ends_at',
            ])
            ->addColumn('next_due_payment', 'datetime', [
                'null' => true,
                'default' => null,
                'after' => 'grace_period_ends',
            ])
            ->addColumn('ends_at', 'timestamp', [
                'null' => true,
                'default' => null,
                'after' => 'next_due_payment',
            ])
            ->addColumn('payment_frequency_id', 'integer', [
                'null' => true,
                'default' => null,
                'limit' => '10',
                'after' => 'ends_at',
            ])
            ->addColumn('trial_ends_days', 'integer', [
                'null' => true,
                'default' => null,
                'limit' => '10',
                'after' => 'payment_frequency_id',
            ])
            ->addColumn('is_freetrial', 'integer', [
                'null' => false,
                'default' => '0',
                'limit' => '3',
                'after' => 'trial_ends_days',
            ])
            ->addColumn('is_active', 'integer', [
                'null' => false,
                'default' => '0',
                'limit' => '3',
                'after' => 'is_freetrial',
            ])
            ->addColumn('is_cancelled', 'integer', [
                'null' => true,
                'default' => '0',
                'limit' => '1',
                'after' => 'is_active',
            ])
            ->addColumn('paid', 'integer', [
                'null' => true,
                'default' => '0',
                'limit' => '3',
                'after' => 'is_cancelled',
            ])
            ->addColumn('charge_date', 'datetime', [
                'null' => true,
                'default' => null,
                'after' => 'paid',
            ])
            ->addColumn('created_at', 'datetime', [
                'null' => false,
                'after' => 'charge_date',
            ])
            ->addColumn('updated_at', 'datetime', [
                'null' => true,
                'default' => null,
                'after' => 'created_at',
            ])
            ->addColumn('is_deleted', 'integer', [
                'null' => false,
                'default' => '0',
                'limit' => '3',
                'after' => 'updated_at',
            ])
            ->addColumn('record_id', 'integer', [
                'null' => true,
                'default' => null,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'is_deleted',
            ])
            ->create();
    }
}
