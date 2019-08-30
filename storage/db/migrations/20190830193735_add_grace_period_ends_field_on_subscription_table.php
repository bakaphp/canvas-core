<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class AddGracePeriodEndsFieldOnSubscriptionTable extends AbstractMigration
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
            ->addColumn('grace_period_ends', 'date', [
                'null' => true,
                'after' => 'is_deleted',
            ])
            ->save();
    }
}
