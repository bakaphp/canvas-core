<?php

use Phinx\Seed\AbstractSeed;

class PaymentFrequenciesSeeder extends AbstractSeed
{
    public function run()
    {
        $data = [
            [
                'name' => 'Monthly',
                'created_at' => date('Y-m-d H:m:s'),
            ],
            [
                'name' => 'Annually',
                'created_at' => date('Y-m-d H:m:s'),
            ]
        ];

        $posts = $this->table('payment_frequencies');
        $posts->insert($data)
              ->save();
    }
}
