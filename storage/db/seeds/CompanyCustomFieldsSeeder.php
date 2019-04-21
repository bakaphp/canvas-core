<?php

use Phinx\Seed\AbstractSeed;

class CompanyCustomFieldsSeeder extends AbstractSeed
{
    public function run()
    {
        $data = [
            [
                'companies_id' => 1,
                'custom_fields_id' => 1,
                'value' => 'example_value',
                'created_at' => date('Y-m-d H:m:s'),
            ],
        ];

        $posts = $this->table('companies_custom_fields');
        $posts->insert($data)
              ->save();
    }
}
