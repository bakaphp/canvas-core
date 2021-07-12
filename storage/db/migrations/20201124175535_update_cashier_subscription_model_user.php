<?php

class UpdateCashierSubscriptionModelUser extends Phinx\Migration\AbstractMigration
{
    public function change()
    {
        $this->table('users', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
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
            ->removeColumn('card_last_four')
            ->removeColumn('card_brand')
            ->removeColumn('trial_ends_at')
            ->save();
    }
}
