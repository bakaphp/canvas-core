<?php

class AddPaymentIntervalsFieldToAppsPlans extends Phinx\Migration\AbstractMigration
{
    public function change()
    {
        $this->table('apps_plans', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addColumn('payment_ìnterval', 'string', [
                'null' => true,
                'default' => null,
                'limit' => 16,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'name',
            ])
            ->save();
    }
}
