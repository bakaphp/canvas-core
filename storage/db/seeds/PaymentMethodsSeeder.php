<?php

use Phinx\Seed\AbstractSeed;

class PaymentMethodsSeeder extends AbstractSeed
{
    public function run()
    {
        $data = [
            [
                'name' => 'Credit Card',
                'is_default' => 1,
                'created_at' => date('Y-m-d H:m:s'),
            ]
        ];

        $posts = $this->table('payment_methods');
        $posts->insert($data)
              ->save();
    }
}
