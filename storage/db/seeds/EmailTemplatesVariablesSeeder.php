<?php

use Phinx\Seed\AbstractSeed;

class EmailTemplatesVariablesSeeder extends AbstractSeed
{
    public function run()
    {
        $data = [
            [
                'users_id' => 1,
                'companies_id' => 0,
                'apps_id' => 0,
                'system_modules_id' => 1,
                'name' => '{{example}}',
                'value' => 'example_content',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'users_id' => 1,
                'companies_id' => 0,
                'apps_id' => 0,
                'system_modules_id' => 1,
                'name' => '{{app-name}}',
                'value' => 'Example Name',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'users_id' => 1,
                'companies_id' => 0,
                'apps_id' => 0,
                'system_modules_id' => 1,
                'name' => '{{base_color}}',
                'value' => '#61c2cc',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'users_id' => 1,
                'companies_id' => 0,
                'apps_id' => 0,
                'system_modules_id' => 1,
                'name' => '{{secondary_color}}',
                'value' => '#9ee5b5',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'users_id' => 1,
                'companies_id' => 0,
                'apps_id' => 0,
                'system_modules_id' => 1,
                'name' => '{{external-link}}',
                'value' => 'http://example.com',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'users_id' => 1,
                'companies_id' => 0,
                'apps_id' => 0,
                'system_modules_id' => 1,
                'name' => '{{external-link-label}}',
                'value' => 'Example External Link',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'users_id' => 1,
                'companies_id' => 0,
                'apps_id' => 0,
                'system_modules_id' => 1,
                'name' => '{{notification-title}}',
                'value' => 'Notification Title',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'users_id' => 1,
                'companies_id' => 0,
                'apps_id' => 0,
                'system_modules_id' => 1,
                'name' => '{{notification-body}}',
                'value' => 'Lorem ipsum dolor sit',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'users_id' => 1,
                'companies_id' => 0,
                'apps_id' => 0,
                'system_modules_id' => 1,
                'name' => '{{unsubscribe-link}}',
                'value' => 'http://unsubscribe.com',
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $posts = $this->table('email_templates_variables');
        $posts->insert($data)
              ->save();
    }
}
