<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class AddIsCancelledToSubscriptionsTable extends AbstractMigration
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
            ->addColumn('is_cancelled', 'integer', [
                'null' => true,
                'default' => '0',
                'limit' => '1',
                'after' => 'is_deleted',
            ])
            ->save();
    }
}
