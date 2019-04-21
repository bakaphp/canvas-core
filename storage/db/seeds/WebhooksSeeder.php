<?php

use Phinx\Seed\AbstractSeed;

class WebhooksSeeder extends AbstractSeed
{
    public function run()
    {
        $data = [
            [
                'system_modules_id' => 1,
                'apps_id' => 1,
                'name' => 'example_name',
                'description' => 'example_description',
                'action' => 'create',
                'format' => 'JSON',
                'created_at' => date('Y-m-d H:m:s'),
            ]
        ];

        $posts = $this->table('webhooks');
        $posts->insert($data)
              ->save();
    }
}
