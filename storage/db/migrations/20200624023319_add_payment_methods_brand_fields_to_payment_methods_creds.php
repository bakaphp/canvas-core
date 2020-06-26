<?php

class AddPaymentMethodsBrandFieldsToPaymentMethodsCreds extends Phinx\Migration\AbstractMigration
{
    public function change()
    {
        $this->table('payment_methods_creds', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addColumn('payment_methods_brand', 'string', [
                'null' => true,
                'default' => null,
                'limit' => 32,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'payment_ending_numbers',
            ])
            ->save();
    }
}
