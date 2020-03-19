<?php

use Phinx\Db\Adapter\MysqlAdapter;

class AddPaymentsFrequencyToAppPlansTable extends Phinx\Migration\AbstractMigration
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
            ->addColumn('payment_frequency', 'integer', [
                'null' => true,
                'default' => '1',
                'limit' => MysqlAdapter::INT_REGULAR,
                'comment' => 'The integers in this field represent months',
                'after' => 'is_deleted',
            ])
            ->save();
    }
}
