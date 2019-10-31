<?php

use Phinx\Db\Adapter\MysqlAdapter;

class GracePeriodEndsDatetime extends Phinx\Migration\AbstractMigration
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
        ->addColumn('grace_period_ends', 'datetime', [
            'null' => true,
            'after' => 'trial_ends_at',
        ])
        ->addColumn('next_due_payment', 'datetime', [
            'null' => true,
            'after' => 'grace_period_ends',
        ])
        ->save();
    }
}
