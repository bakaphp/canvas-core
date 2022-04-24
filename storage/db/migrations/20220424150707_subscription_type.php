<?php

use Phinx\Db\Adapter\MysqlAdapter;

class SubscriptionType extends Phinx\Migration\AbstractMigration
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
            ->addColumn('companies_branches_id', 'integer', [
                'null' => false,
                'limit' => '10',
                'after' => 'companies_id',
            ])
            ->addColumn('subscription_types_id', 'boolean', [
                'null' => false,
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'apps_id',
            ])
            ->addIndex(['companies_branches_id'], [
                'name' => 'companies_branches_id',
                'unique' => false,
            ])
            ->addIndex(['subscription_types_id'], [
                'name' => 'subscription_type_id',
                'unique' => false,
            ])
            ->save();

        $this->table('apps', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'COMPACT',
        ])
            ->addColumn('subscription_types_id', 'boolean', [
                'null' => true,
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'payments_active',
            ])
            ->addIndex(['subscription_types_id'], [
                'name' => 'subscription_type',
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
            ->addColumn('stripe_id', 'string', [
                'null' => true,
                'limit' => 255,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'email',
            ])
            ->addIndex(['stripe_id'], [
                'name' => 'stripe_id',
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
            'row_format' => 'DYNAMIC',
        ])
            ->addColumn('stripe_id', 'string', [
                'null' => true,
                'limit' => 255,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'zipcode',
            ])
            ->addIndex(['stripe_id'], [
                'name' => 'stripe_id',
                'unique' => false,
            ])
            ->save();
    }
}
