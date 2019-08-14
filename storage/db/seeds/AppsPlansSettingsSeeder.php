<?php

use Phinx\Seed\AbstractSeed;

class AppsPlansSettingsSeeder extends AbstractSeed
{
    public function run()
    {
        $data = [
            [
                'apps_plans_id'=>1,
                'apps_id'=>1,
                'key'=>'example123456',
                'value'=>1,
                'created_at' => date('Y-m-d H:m:s')
            ]
        ];

        $posts = $this->table('apps_plans_settings');
        $posts->insert($data)
              ->save();
    }
}
