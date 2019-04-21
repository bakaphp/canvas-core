<?php

use Phinx\Seed\AbstractSeed;

class CustomFieldsSeeder extends AbstractSeed
{
    public function run()
    {
        $data = [
            [
                'users_id' => 1,
                'companies_id' => 1,
                'apps_id' => 1,
                'name' => 'example_field',
                'custom_fields_modules_id' => 1,
                'fields_type_id' => 1,
                'created_at' => date('Y-m-d H:m:s'),
            ],
            [
                'users_id' => 2,
                'companies_id' => 3,
                'apps_id' => 1,
                'name' => 'example_custom_field',
                'custom_fields_modules_id' => 1,
                'fields_type_id' => 1,
                'created_at' => date('Y-m-d H:m:s'),
            ]
        ];

        $posts = $this->table('custom_fields');
        $posts->insert($data)
              ->save();
    }
}
