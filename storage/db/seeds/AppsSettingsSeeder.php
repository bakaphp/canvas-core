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
                'name' => 'allow_user_registration',
                'value' => '1',
                'created_at' => date('Y-m-d H:m:s'),
            ],
            [
                'apps_id' => 1,
                'name' => 'background_image',
                'value' => 'https://mc-canvas.s3.amazonaws.com/default-background-auth.jpg',
                'created_at' => date('Y-m-d H:m:s'),
            ],
            [
                'apps_id' => 1,
                'name' => 'logo',
                'value' => 'https://mc-canvas.s3.amazonaws.com/gewaer-logo-dark.png',
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
                'value' => 'https://mc-canvas.s3.amazonaws.com/gewaer-logo-dark.png',
                'created_at' => date('Y-m-d H:m:s'),
            ],
            [
                'apps_id' => 1,
                'name' => 'titles',
                'value' => 'Example Title',
                'created_at' => date('Y-m-d H:m:s'),
            ],[
                'apps_id' => 1,
                'name' => 'base_color',
                'value' => '#61c2cc',
                'created_at' => date('Y-m-d H:m:s'),
            ],
            [
                'apps_id' => 1,
                'name' => 'secondary_color',
                'value' => '#9ee5b5',
                'created_at' => date('Y-m-d H:m:s'),
            ]
        ];

        $posts = $this->table('apps_settings');
        $posts->insert($data)
            ->save();
    }
}
