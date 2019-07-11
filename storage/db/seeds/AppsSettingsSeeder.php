<?php

use Phinx\Seed\AbstractSeed;

class AppsSettingsSeeder extends AbstractSeed
{
    public function run()
    {
        $data = [
            [
                'apps_id' => 1,
                'name' => 'language',
                'value' => 'EN',
                'created_at' => date('Y-m-d H:m:s'),
            ],
            [
                'apps_id' => 1,
                'name' => 'timezone',
                'value' => 'America/New_York',
                'created_at' => date('Y-m-d H:m:s'),
            ],
            [
                'apps_id' => 1,
                'name' => 'currency',
                'value' => 'USD',
                'created_at' => date('Y-m-d H:m:s'),
            ],
            [
                'apps_id' => 1,
                'name' => 'filesystem',
                'value' => 'local',
                'created_at' => date('Y-m-d H:m:s'),
            ],
            [
                'apps_id' => 1,
                'name' => 'subscription-based',
                'value' => '1',
                'created_at' => date('Y-m-d H:m:s'),
            ],
            [
                'apps_id' => 1,
                'name' => 'bg-image',
                'value' => 'Example Image',
                'created_at' => date('Y-m-d H:m:s'),
            ],
            [
                'apps_id' => 1,
                'name' => 'logo',
                'value' => 'Example Logo Image',
                'created_at' => date('Y-m-d H:m:s'),
            ],
            [
                'apps_id' => 1,
                'name' => 'registered',
                'value' => 1,
                'created_at' => date('Y-m-d H:m:s'),
            ],
            [
                'apps_id' => 1,
                'name' => 'favicon',
                'value' => 'Example Image',
                'created_at' => date('Y-m-d H:m:s'),
            ],
            [
                'apps_id' => 1,
                'name' => 'titles',
                'value' => 'Example Title',
                'created_at' => date('Y-m-d H:m:s'),
            ],
            [
                'apps_id' => 1,
                'name' => 'has-subscriptions',
                'value' => 1,
                'created_at' => date('Y-m-d H:m:s'),
            ]
        ];

        $posts = $this->table('apps_settings');
        $posts->insert($data)
            ->save();
    }
}
