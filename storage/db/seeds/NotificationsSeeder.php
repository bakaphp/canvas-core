<?php

use Phinx\Seed\AbstractSeed;

class NotificationsSeeder extends AbstractSeed
{
    public function run()
    {
        $data = [
            [
                'name' => 'System',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Apps',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Users',
                'created_at' => date('Y-m-d H:m:s')
            ]
        ];

        $posts = $this->table('notification_types');
        $posts->insert($data)
              ->save();
    }
}
