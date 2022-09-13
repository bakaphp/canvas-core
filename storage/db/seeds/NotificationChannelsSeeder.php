<?php

use Phinx\Seed\AbstractSeed;

class NotificationChannelsSeeder extends AbstractSeed
{
    public function run()
    {
        $data = [
            [
                'name' => 'Email',
                'slug' => 'email',
                'created_at' => date('Y-m-d H:m:s'),
            ],
            [
                'name' => 'Push',
                'slug' => 'push',
                'created_at' => date('Y-m-d H:m:s'),
            ],
            [
                'name' => 'Realtime',
                'slug' => 'realtime',
                'created_at' => date('Y-m-d H:m:s'),
            ]
        ];

        $posts = $this->table('notifications_channels');
        $posts->insert($data)
              ->save();
    }
}
