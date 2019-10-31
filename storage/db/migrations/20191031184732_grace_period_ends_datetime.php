<?php

use Phinx\Db\Adapter\MysqlAdapter;

class GracePeriodEndsDatetime extends Phinx\Migration\AbstractMigration
{
    public function change()
    {
        $this->table('banlist', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8',
            'collation' => 'utf8_bin',
            'comment' => '',
            'row_format' => 'COMPACT',
        ])
            ->save();
        $this->table('sessions', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'COMPACT',
        ])
            ->save();
        $this->table('user_config', [
            'id' => false,
            'primary_key' => ['users_id', 'name'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'COMPACT',
        ])
            ->save();
        $this->table('session_keys', [
            'id' => false,
            'primary_key' => ['sessions_id', 'users_id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'COMPACT',
        ])
            ->save();
        $this->table('subscriptions', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addColumn('grace_period_ends', 'datetime', [
                'null' => true,
                'after' => 'trial_ends_at',
            ])
            ->addColumn('next_due_payment', 'datetime', [
                'null' => true,
                'after' => 'grace_period_ends',
            ])
        ->changeColumn('ends_at', 'timestamp', [
            'null' => true,
            'after' => 'next_due_payment',
        ])
        ->changeColumn('payment_frequency_id', 'integer', [
            'null' => true,
            'limit' => '10',
            'after' => 'ends_at',
        ])
        ->changeColumn('trial_ends_days', 'integer', [
            'null' => true,
            'limit' => '10',
            'after' => 'payment_frequency_id',
        ])
        ->changeColumn('is_freetrial', 'integer', [
            'null' => false,
            'default' => '0',
            'limit' => '3',
            'after' => 'trial_ends_days',
        ])
        ->changeColumn('is_active', 'integer', [
            'null' => false,
            'default' => '0',
            'limit' => '3',
            'after' => 'is_freetrial',
        ])
        ->changeColumn('is_cancelled', 'integer', [
            'null' => true,
            'default' => '0',
            'limit' => '1',
            'after' => 'is_active',
        ])
        ->changeColumn('paid', 'integer', [
            'null' => true,
            'default' => '0',
            'limit' => '3',
            'after' => 'is_cancelled',
        ])
        ->changeColumn('charge_date', 'datetime', [
            'null' => true,
            'after' => 'paid',
        ])
        ->changeColumn('created_at', 'datetime', [
            'null' => false,
            'after' => 'charge_date',
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
        $this->table('user_linked_sources', [
            'id' => false,
            'primary_key' => ['users_id', 'source_id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'COMPACT',
        ])
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
        ->changeColumn('companies_id', 'integer', [
            'null' => false,
            'limit' => '10',
            'comment' => "las apps que tiene contra\xc3\xadda o usando el usuario\n\n- leads\n- agents\n- office\n- etc",
        ])
            ->save();
        $this->table('companies', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => "\t\t\t\t\t",
            'row_format' => 'COMPACT',
        ])
            ->save();
        $this->table('companies_settings', [
            'id' => false,
            'primary_key' => ['companies_id', 'name'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'COMPACT',
        ])
        ->changeColumn('companies_id', 'integer', [
            'null' => false,
            'limit' => '10',
            'comment' => "tabla donde se guardan las configuraciones en key value de los diferentes modelos\n\n- general, zoho key, mandrill email settings\n- modulo leads, agent default, rotation default , etc",
        ])
            ->save();
        $this->table('sources', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'COMPACT',
        ])
            ->save();
        $this->table('apps_roles', [
            'id' => false,
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'COMPACT',
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
            ->save();
        $this->table('custom_fields', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'COMPACT',
        ])
            ->save();
        $this->table('users_associated_company', [
            'id' => false,
            'primary_key' => ['users_id', 'companies_id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'COMPACT',
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
            ->save();
    }
}
