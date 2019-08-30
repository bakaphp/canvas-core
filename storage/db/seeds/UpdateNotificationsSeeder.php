<?php

use Phinx\Seed\AbstractSeed;

class UpdateNotificationsSeeder extends AbstractSeed
{
    public function run()
    {
        $data = [
            [
                'name' => 'Invitation',
                'apps_id' => 1,
                'system_modules_id' => 1,
                'key' => 'Canvas\\Notifications\\Invitation',
                'description' => 'Send user invitation',
                'with_realtime' => 0,
                'created_at' => date('Y-m-d H:m:s')
            ]
        ];

        $posts = $this->table('notification_types');
        $posts->insert($data)
              ->save();
    }
}
