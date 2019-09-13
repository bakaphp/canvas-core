u<?php

use Phinx\Seed\AbstractSeed;
use Phalcon\Security\Random;

class InitGewaer extends AbstractSeed
{
    public function run()
    {
        $random = new Random();
        //add default languages
        $data = [
            [
                'name' => 'Default',
                'key' => 'mQsVRvorhqBJOijxkC4MB4hHFVcVTJia',
                'is_public' => 1,
                'description' => 'Gewaer Ecosystem',
                'created_at' => date('Y-m-d H:i:s'),
                'default_apps_plan_id' => 1,
                'payments_active' => 1,
                'is_actived' => 1,
                'is_deleted' => 0
            ], [
                'name' => 'CRM',
                'key' => $random->uuid(),
                'is_public' => 1,
                'description' => 'CRM App',
                'created_at' => date('Y-m-d H:i:s'),
                'default_apps_plan_id' => 1,
                'payments_active' => 1,
                'is_actived' => 1,
                'is_deleted' => 0
            ]
        ];

        $table = $this->table('apps');
        $table->insert($data)->save();

        //add default companies
        $data = [
            [
                'name' => 'Canvas',
                'users_id' => 1,
                'system_modules_id' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'is_deleted' => 0
            ], [
                'name' => 'CRM',
                'users_id' => 1,
                'system_modules_id' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'is_deleted' => 0
            ],
        ];

        $table = $this->table('companies');
        $table->insert($data)->save();

        //add default companies
        $data = [
            [
                'name' => 'Default',
                'users_id' => 1,
                'address' => 'default',
                'zipcode' => '32234',
                'email' => 'default@default.com',
                'phone' => '123142341',
                'companies_id' => 1,
                'is_default' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'is_deleted' => 0
            ]
        ];

        $table = $this->table('companies_branches');
        $table->insert($data)->save();

        //add source
        $data = [
            [
                'title' => 'baka',
                'url' => 'baka.io',
                'created_at' => date('Y-m-d H:i:s'),
                'is_deleted' => 0
            ], [
                'title' => 'androipapp',
                'url' => 'bakaapp.io',
                'created_at' => date('Y-m-d H:i:s'),
                'is_deleted' => 0
            ], [
                'title' => 'iosapp',
                'url' => 'bakaios.io',
                'created_at' => date('Y-m-d H:i:s'),
                'is_deleted' => 0
            ], [
                'title' => 'google',
                'url' => 'google.com',
                'created_at' => date('Y-m-d H:i:s'),
                'is_deleted' => 0
            ], [
                'title' => 'facebook',
                'url' => 'facebook.com',
                'created_at' => date('Y-m-d H:i:s'),
                'is_deleted' => 0
            ],
        ];

        $table = $this->table('sources');
        $table->insert($data)->save();

        //add default languages
        $data = [
            [
                'user_activation_email' => $random->uuid(),
                'email' => 'nobody@baka.io',
                'password' => password_hash('bakatest123567', PASSWORD_DEFAULT),
                'firstname' => 'Baka',
                'lastname' => 'Idiot',
                'default_company' => 1,
                'displayname' => 'nobody',
                'system_modules_id' => 2,
                'default_company_branch' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'status' => 1,
                'user_active' => 1,
                'is_deleted' => 0
            ]
        ];

        $table = $this->table('users');
        $table->insert($data)->save();

        //add default languages
        $data = [
            [
                'name' => 'Admins',
                'description' => 'System Administrator',
                'scope' => 0,
                'companies_id' => 1,
                'apps_id' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'is_deleted' => 0
            ], [
                'name' => 'Users',
                'description' => 'Normal Users can (CRUD)',
                'scope' => 0,
                'companies_id' => 1,
                'apps_id' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'is_deleted' => 0
            ], [
                'name' => 'Agents',
                'description' => 'Agents Users can (CRU)',
                'scope' => 0,
                'companies_id' => 1,
                'apps_id' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'is_deleted' => 0
            ]
        ];

        $table = $this->table('roles');
        $table->insert($data)->save();

        //add default languages
        $data = [
            [
                'id' => 'EN',
                'name' => 'English',
                'title' => 'English',
                'order' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'is_deleted' => 0
            ], [
                'id' => 'ES',
                'name' => 'Español',
                'title' => 'Español',
                'order' => 2,
                'created_at' => date('Y-m-d H:i:s'),
                'is_deleted' => 0
            ]
        ];

        $table = $this->table('languages');
        $table->insert($data)->save();

        //add default languages
        $data = [
            [
                'apps_id' => '1',
                'name' => 'monthly-10-1',
                'description' => 'monthly-10-1',
                'stripe_id' => 'monthly-10-1',
                'stripe_plan' => 'monthly-10-1',
                'pricing' => 10,
                'pricing_anual' => 100,
                'currency_id' => 1,
                'free_trial_dates' => 14,
                'is_default' => 1
            ], [
                'apps_id' => '1',
                'name' => 'monthly-10-2',
                'description' => 'monthly-10-2',
                'stripe_id' => 'monthly-10-2',
                'stripe_plan' => 'monthly-10-2',
                'pricing' => 100,
                'pricing_anual' => 1000,
                'currency_id' => 1,
                'free_trial_dates' => 14,
                'is_default' => 0
            ],
        ];

        $table = $this->table('apps_plans');
        $table->insert($data)->save();

        $data = [
            [
                'id' => 1,
                'name' => 'Companies',
                'slug' => 'companies',
                'model_name' => 'Canvas\Models\Companies',
                'apps_id' => 1,
                'parents_id' => 0,
                'menu_order' => 1,
                'use_elastic' => 0,
                'browse_fields' => '[
                    {
                        "name": "name",
                        "title": "Name",
                        "sortField": "name",
                        "filterable": true,
                        "searchable": true
                    },
                    {
                        "name": "address",
                        "title": "Address",
                        "sortField": "address",
                        "filterable": true,
                        "searchable": true
                    },
                    {
                        "name": "timezone",
                        "title": "Timezone",
                        "sortField": "timezone",
                        "filterable": true,
                        "searchable": true
                    },
                    {
                        "name": "website",
                        "title": "Website",
                        "sortField": "website",
                        "filterable": true,
                        "searchable": true
                    }
                ]',
                'show' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ], [
                'id' => 2,
                'name' => 'Users',
                'slug' => 'users',
                'model_name' => 'Canvas\Models\Users',
                'apps_id' => 1,
                'parents_id' => 0,
                'menu_order' => 2,
                'show' => 1,
                'use_elastic' => 0,
                'browse_fields' => '[
                    {
                        "name": "firstname",
                        "title": "First Name",
                        "sortField": "firstname",
                        "filterable": true,
                        "searchable": true
                    },
                    {
                        "name": "lastname",
                        "title": "Last Name",
                        "sortField": "lastname",
                        "filterable": true,
                        "searchable": true
                    },
                    {
                        "name": "email",
                        "title": "Email",
                        "sortField": "email",
                        "filterable": true,
                        "searchable": true
                    },
                    {
                        "name": "displayname",
                        "title": "Display Name",
                        "sortField": "displayname",
                        "filterable": true,
                        "searchable": true
                    }
                ]',
                'created_at' => date('Y-m-d H:i:s')
            ]
        ];

        $table = $this->table('system_modules');
        $table->insert($data)->save();

        $data = [
            [
                'id' => 1,
                'name' => 'text',
                'description' => 'Any Text type of area, input , etc',
                'created_at' => date('Y-m-d H:i:s')
            ], [
                'id' => 2,
                'name' => 'list',
                'description' => 'Dropdown lists',
                'created_at' => date('Y-m-d H:i:s')
            ]
        ];

        $table = $this->table('custom_fields_types');
        $table->insert($data)->save();
    }
}
