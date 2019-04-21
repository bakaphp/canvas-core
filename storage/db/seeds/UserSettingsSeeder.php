<?php

use Phinx\Seed\AbstractSeed;

class UserSettingsSeeder extends AbstractSeed
{
    public function run()
    {
        $data = [
            [
                'name' => 'US',
                'created_at' => date('Y-m-d H:m:s'),
            ]
        ];

        $posts = $this->table('locales');
        $posts->insert($data)
              ->save();
    }
}
