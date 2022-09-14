<?php

use Phinx\Seed\AbstractSeed;

class NotificationChannelsSeeder extends AbstractSeed
{
    public function run()
    {
        $data = [
            [
                'name' => 'Email',
                'name' => 'email',
                'created_at' => date('Y-m-d H:m:s'),
            ],
            [
                'name' => 'Push',
                'name' => 'push',
                'created_at' => date('Y-m-d H:m:s'),
            ],
            [
                'name' => 'Realtime',
                'name' => 'realtime',
                'created_at' => date('Y-m-d H:m:s'),
            ]
        ];

        $posts = $this->table('notifications_channels');
        $posts->insert($data)
              ->save();
    }
}
