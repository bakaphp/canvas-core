<?php

use Phinx\Seed\AbstractSeed;

class UserInvitesSeeder extends AbstractSeed
{
    public function run()
    {
        $data = [
            [
                'users_id' => 1,
                'companies_id' => 0,
                'apps_id' => 0,
                'name' => 'users-invite',
                'template' => '{link}',
                'created_at' => date('Y-m-d H:i:s'),
            ], [
                'users_id' => 1,
                'companies_id' => 0,
                'apps_id' => 0,
                'name' => 'users-registration',
                'template' => '{link}',
                'created_at' => date('Y-m-d H:i:s'),
            ], [
                'users_id' => 2,
                'companies_id' => 3,
                'apps_id' => 1,
                'name' => 'users-invite',
                'template' => '{link}',
                'created_at' => date('Y-m-d H:i:s'),
            ], [
                'users_id' => 2,
                'companies_id' => 3,
                'apps_id' => 1,
                'name' => 'users-registration',
                'template' => '{link}',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'users_id' => 1,
                'companies_id' => 0,
                'apps_id' => 0,
                'name' => 'users-charge-success',
                'template' => '{link}',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'users_id' => 1,
                'companies_id' => 0,
                'apps_id' => 0,
                'name' => 'users-charge-failed',
                'template' => '{link}',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'users_id' => 1,
                'companies_id' => 0,
                'apps_id' => 0,
                'name' => 'users-charge-pending',
                'template' => '{link}',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'users_id' => 1,
                'companies_id' => 0,
                'apps_id' => 0,
                'name' => 'users-trial-end',
                'template' => '{link}',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'users_id' => 1,
                'companies_id' => 0,
                'apps_id' => 0,
                'name' => 'users-subscription-updated',
                'template' => '{link}',
                'created_at' => date('Y-m-d H:i:s'),
            ]
        ];

        $posts = $this->table('email_templates');
        $posts->insert($data)
              ->save();
    }
}
