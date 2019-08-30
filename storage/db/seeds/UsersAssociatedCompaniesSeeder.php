<?php

use Phinx\Seed\AbstractSeed;

class UsersAssociatedCompaniesSeeder extends AbstractSeed
{
    public function run()
    {
        $data = [
            [
                'users_id'=>1,
                'companies_id'=>1,
                'identify_id'=>1,
                'user_active'=>1,
                'user_role'=>1,
                'created_at' => date('Y-m-d H:m:s')
            ]
        ];

        $posts = $this->table('users_associated_company');
        $posts->insert($data)
              ->save();
    }
}
