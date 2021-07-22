<?php

use Phinx\Seed\AbstractSeed;

class UserConfigSeeder extends AbstractSeed
{
    public function run()
    {
        $data = [
            [
                'users_id'=>1,
                'name' => 'DefaulCompanyApp_1',
                'value'=> 1,
                'created_at' => date('Y-m-d H:m:s'),
            ]
        ];

        $posts = $this->table('user_config');
        $posts->insert($data)
              ->save();
    }
}
