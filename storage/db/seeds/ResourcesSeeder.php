<?php

use Phinx\Seed\AbstractSeed;

class ResourcesSeeder extends AbstractSeed
{
    public function run()
    {
        $data = [
            [
                'name' => 'CompanyBranches',
                'description' => 'CompanyBranches',
                'apps_id' => 1,
                'created_at' => date('Y-m-d H:m:s'),
            ],
            [
                'name' => 'CompanyUsers',
                'description' => 'CompanyUsers',
                'apps_id' => 1,
                'created_at' => date('Y-m-d H:m:s'),
            ],
            [
                'name' => 'CompanyRoles',
                'description' => 'CompanyRoles',
                'apps_id' => 1,
                'created_at' => date('Y-m-d H:m:s'),
            ],
            [
                'name' => 'CompanySubscriptions',
                'description' => 'CompanySubscriptions',
                'apps_id' => 1,
                'created_at' => date('Y-m-d H:m:s'),
            ],
            [
                'name' => 'CustomFields',
                'description' => 'CustomFields',
                'apps_id' => 1,
                'created_at' => date('Y-m-d H:m:s'),
            ],
            [
                'name' => 'CompaniesManager',
                'description' => 'CompaniesManager',
                'apps_id' => 1,
                'created_at' => date('Y-m-d H:m:s'),
            ],
            [
                'name' => 'Apps-plans',
                'description' => 'Apps-plans',
                'apps_id' => 1,
                'created_at' => date('Y-m-d H:m:s'),
            ],
        ];

        $posts = $this->table('resources');
        $posts->insert($data)
              ->save();
    }
}
