<?php

use Phinx\Seed\AbstractSeed;

class CompaniesSettingsSeeder extends AbstractSeed
{
    public function run()
    {
        $data = [
            [
                'companies_id' => 1,
                'name' => 'notifications',
                'value' => 'nobody@baka.io',
                'created_at' => date('Y-m-d H:m:s'),
                'is_deleted'=>0
            ],
            [
                'companies_id' => 1,
                'name' => 'paid',
                'value' => '1',
                'created_at' => date('Y-m-d H:m:s'),
                'is_deleted'=>0
            ],
            [
                'companies_id' => 1,
                'name' => 'payment_gateway_customer_id',
                'value' => 'Example customer id',
                'created_at' => date('Y-m-d H:m:s'),
                'is_deleted'=>0
            ]
        ];

        $posts = $this->table('companies_settings');
        $posts->insert($data)
              ->save();
    }
}
