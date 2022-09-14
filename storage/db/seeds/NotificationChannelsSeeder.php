<?php

use Phinx\Seed\AbstractSeed;

class NotificationChannelsSeeder extends AbstractSeed
{
    public function run()
    {
        $data = [
            [
                'name' => 'Email',
                'created_at' => date('Y-m-d H:m:s'),
            ],
            [
                'name' => 'Push',
                'created_at' => date('Y-m-d H:m:s'),
            ],
            [
                'name' => 'Realtime',
                'created_at' => date('Y-m-d H:m:s'),
            ]
        ];

        $posts = $this->table('notifications_channels');
        $posts->insert($data)
              ->save();
    }
}
