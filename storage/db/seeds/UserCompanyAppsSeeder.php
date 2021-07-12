<?php

use Phinx\Seed\AbstractSeed;

class UserCompanyAppsSeeder extends AbstractSeed
{
    public function run()
    {
        $data = [
            [
                'companies_id' => 1,
                'apps_id' => 1,
                'created_at' => date('Y-m-d H:m:s'),
                'is_deleted' => 0
            ]
        ];

        $posts = $this->table('user_company_apps');
        $posts->insert($data)
              ->save();
    }
}
