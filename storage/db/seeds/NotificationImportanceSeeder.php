<?php

use Phinx\Seed\AbstractSeed;

class NotificationImportanceSeeder extends AbstractSeed
{
    public function run()
    {
        $data = [
            [

                'apps_id' => 1,
                'name' => 'all',
                'validation_expression' => 'id>0',
            ]
        ];

        $posts = $this->table('notifications_importance');
        $posts->insert($data)
              ->save();
    }
}
