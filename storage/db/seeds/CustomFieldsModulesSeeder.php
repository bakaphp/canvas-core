<?php

use Phinx\Seed\AbstractSeed;

class CustomFieldsModulesSeeder extends AbstractSeed
{
    public function run()
    {
        $data = [
            [
                'apps_id' => 1,
                'name' => 'companies',
                'model_name' => 'Canvas\Models\Companies',
                'created_at' => date('Y-m-d H:m:s'),
            ]
        ];

        $posts = $this->table('custom_fields_modules');
        $posts->insert($data)
              ->save();
    }
}
