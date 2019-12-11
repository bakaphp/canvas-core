<?php

use Phinx\Seed\AbstractSeed;

class NotificationsSeeder extends AbstractSeed
{
    public function run()
    {
        $data = [
            [
                'name' => 'Apps',
                'apps_id' => 1,
                'system_modules_id' => 1,
                'key' => 'Canvas\\Notifications\\Apps',
                'description' => 'Apps Notification',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'System',
                'apps_id' => 1,
                'system_modules_id' => 1,
                'key' => 'Canvas\\Notifications\\System',
                'description' => 'System Notification',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Signup',
                'apps_id' => 1,
                'system_modules_id' => 1,
                'key' => 'Canvas\\Notifications\\Signup',
                'description' => 'Signup Notification',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Subscription',
                'apps_id' => 1,
                'system_modules_id' => 1,
                'key' => 'Canvas\\Notifications\\Subscription',
                'description' => 'Subscription Notification',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Invitation',
                'apps_id' => 1,
                'system_modules_id' => 1,
                'key' => 'Canvas\\Notifications\\Invitation',
                'description' => 'Invitation Notification',
                'created_at' => date('Y-m-d H:m:s')
            ]
        ];

        $posts = $this->table('notification_types');
        $posts->insert($data)
              ->save();
    }
}
