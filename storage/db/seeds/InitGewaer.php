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
                'ecosystem_auth' => 1,
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
                'ecosystem_auth' => 1,
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
            [
                'title' => 'github',
                'url' => 'github.com',
                'created_at' => date('Y-m-d H:i:s'),
                'is_deleted' => 0
            ],
            [
                'title' => 'apple',
                'url' => 'apple.com',
                'created_at' => date('Y-m-d H:i:s'),
                'is_deleted' => 0
            ]
        ];

        $table = $this->table('sources');
        $table->insert($data)->save();

        //add default languages
        $data = [
            [
                'id' => -1,
                'user_activation_email' => $random->uuid(),
                'email' => 'anonymous@baka.io',
                'password' => password_hash('bakatest123567', PASSWORD_DEFAULT),
                'firstname' => 'Anonymous',
                'lastname' => 'Anonymous',
                'default_company' => 1,
                'displayname' => 'anonymous',
                'system_modules_id' => 2,
                'default_company_branch' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'status' => 1,
                'user_active' => 1,
                'is_deleted' => 0
            ],
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
                'is_default' => 1,
                'payment_frequencies_id' => 1
            ], [
                'apps_id' => '1',
                'name' => 'monthly-10-2',
                'description' => 'monthly-10-2',
                'stripe_id' => 'monthly-10-2',
                'stripe_plan' => 'monthly-10-2',
                'pricing' => 100,
                'pricing_anual' => 100,
                'currency_id' => 1,
                'free_trial_dates' => 14,
                'is_default' => 0,
                'payment_frequencies_id' => 1
            ],
            [
                'apps_id' => '1',
                'name' => 'yearly-10-1',
                'description' => 'yearly-10-1',
                'stripe_id' => 'yearly-10-1',
                'stripe_plan' => 'yearly-10-1',
                'pricing' => 100,
                'pricing_anual' => 60,
                'currency_id' => 1,
                'free_trial_dates' => 14,
                'is_default' => 1,
                'payment_frequencies_id' => 2
            ], [
                'apps_id' => '1',
                'name' => 'yearly-10-2',
                'description' => 'yearly-10-2',
                'stripe_id' => 'yearly-10-2',
                'stripe_plan' => 'yearly-10-2',
                'pricing' => 1000,
                'pricing_anual' => 60,
                'currency_id' => 1,
                'free_trial_dates' => 14,
                'is_default' => 0,
                'payment_frequencies_id' => 2
            ]
        ];

        $table = $this->table('apps_plans');
        $table->insert($data)->save();

        $data = [
            [
                'name' => 'Companies',
                'slug' => 'companies',
                'model_name' => 'Canvas\\Models\\Companies',
                'apps_id' => '1',
                'parents_id' => '0',
                'menu_order' => null,
                'show' => '1',
                'use_elastic' => '0',
                'browse_fields' => '[{"name":"name","title":"Name","sortField":"name","filterable":true,"searchable":true},{"name":"address","title":"Address","sortField":"address","filterable":true,"searchable":true},{"name":"timezone","title":"Timezone","sortField":"timezone","filterable":true,"searchable":true},{"name":"website","title":"Website","sortField":"website","filterable":true,"searchable":true}]',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => null,
                'is_deleted' => '0',
                'mobile_component_type' => null,
                'mobile_navigation_type' => null,
                'mobile_tab_index' => '0'
            ],
            [
                'name' => 'Users',
                'slug' => 'users',
                'model_name' => 'Canvas\\Models\\Users',
                'apps_id' => '1',
                'parents_id' => '0',
                'menu_order' => null,
                'show' => '1',
                'use_elastic' => '0',
                'browse_fields' => '[{"name":"firstname","title":"First Name","sortField":"firstname","filterable":true,"searchable":true},{"name":"lastname","title":"Last Name","sortField":"lastname","filterable":true,"searchable":true},{"name":"email","title":"Email","sortField":"email","filterable":true,"searchable":true},{"name":"displayname","title":"Display Name","sortField":"displayname","filterable":true,"searchable":true}]',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => null,
                'is_deleted' => '0',
                'mobile_component_type' => null,
                'mobile_navigation_type' => null,
                'mobile_tab_index' => '0'
            ],
            [
                'name' => 'Companies Branches',
                'slug' => 'companies-branches',
                'model_name' => 'Canvas\\Models\\CompaniesBranches',
                'apps_id' => '1',
                'parents_id' => '0',
                'menu_order' => null,
                'show' => '0',
                'use_elastic' => '0',
                'browse_fields' => '[{"name":"name","title":"Name","sortField":"name","filterable":true,"searchable":true}]',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => null,
                'is_deleted' => '0',
                'mobile_component_type' => null,
                'mobile_navigation_type' => null,
                'mobile_tab_index' => '0'
            ],
            [
                'name' => 'Active Users',
                'slug' => 'users-active',
                'model_name' => 'Canvas\\Models\\Users',
                'apps_id' => '1',
                'parents_id' => '0',
                'menu_order' => null,
                'show' => '0',
                'use_elastic' => '0',
                'browse_fields' => '[{"name":"firstname","title":"First Name","sortField":"firstname","filterable":true,"searchable":true},{"name":"lastname","title":"Last Name","sortField":"lastname","filterable":true,"searchable":true},{"name":"email","title":"Email","sortField":"email","filterable":true,"searchable":true},{"name":"roles.0.name","title":"Roles","sortField":"roles_id","filterable":true},{"name":"lastvisit","title":"Last Visit","sortField":"lastvisit","filterable":true,"searchable":true}]',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => null,
                'is_deleted' => '0',
                'mobile_component_type' => null,
                'mobile_navigation_type' => null,
                'mobile_tab_index' => '0'
            ],
            [
                'name' => 'Invited Users',
                'slug' => 'users-invited',
                'model_name' => 'Canvas\\Models\\Users',
                'apps_id' => '1',
                'parents_id' => '0',
                'menu_order' => null,
                'show' => '0',
                'use_elastic' => '0',
                'browse_fields' => '[{"name":"email","title":"Email","sortField":"email","filterable":true,"searchable":true},{"name":"roles.0.name","title":"Roles","sortField":"roles_id","filterable":true}]',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => null,
                'is_deleted' => '0',
                'mobile_component_type' => null,
                'mobile_navigation_type' => null,
                'mobile_tab_index' => '0'
            ],
            [
                'name' => 'Inactive Users',
                'slug' => 'users-inactive',
                'model_name' => 'Canvas\\Models\\Users',
                'apps_id' => '1',
                'parents_id' => '0',
                'menu_order' => null,
                'show' => '0',
                'use_elastic' => '0',
                'browse_fields' => '[{"name":"firstname","title":"First Name","sortField":"firstname","filterable":true,"searchable":true},{"name":"lastname","title":"Last Name","sortField":"lastname","filterable":true,"searchable":true},{"name":"email","title":"Email","sortField":"email","filterable":true,"searchable":true},{"name":"roles.0.name","title":"Roles","sortField":"roles_id","filterable":true},{"name":"lastvisit","title":"Last Visit","sortField":"lastvisit","filterable":true,"searchable":true}]',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => null,
                'is_deleted' => '0',
                'mobile_component_type' => null,
                'mobile_navigation_type' => null,
                'mobile_tab_index' => '0'
            ],
            [
                'name' => 'Roles',
                'slug' => 'roles',
                'model_name' => 'Canvas\\Models\\Roles',
                'apps_id' => '1',
                'parents_id' => '0',
                'menu_order' => null,
                'show' => '0',
                'use_elastic' => '0',
                'browse_fields' => '[{"name":"name","title":"Name","sortField":"name","filterable":true,"searchable":true},{"name":"description","title":"Description"}]',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => null,
                'is_deleted' => '0',
                'mobile_component_type' => null,
                'mobile_navigation_type' => null,
                'mobile_tab_index' => '0'
            ],
            [
                'name' => 'Custom Fields Modules',
                'slug' => 'custom-fields-modules',
                'model_name' => 'Canvas\\Models\\CustomFieldsModules',
                'apps_id' => '1',
                'parents_id' => '0',
                'menu_order' => null,
                'show' => '0',
                'use_elastic' => '0',
                'browse_fields' => '[{"name":"name","title":"Module","sortField":"name","filterable":true,"searchable":true}]',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => null,
                'is_deleted' => '0',
                'mobile_component_type' => null,
                'mobile_navigation_type' => null,
                'mobile_tab_index' => '0'
            ]
        ];

        $table = $this->table('system_modules');
        $table->insert($data)->save();

        $data = [
            [
                'id' => 1,
                'name' => 'text',
                'description' => 'Regular input field. Any text.',
                'icon' => 'fas fa-sort',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'id' => 2,
                'name' => 'select',
                'description' => 'Dropdown lists',
                'icon' => 'fas fa-sort',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'id' => 3,
                'name' => 'number',
                'description' => 'Whole numbers',
                'icon' => 'fas fa-sort-numeric-down',
                'created_at' => date('Y-m-d H:i:s')
            ]
        ];

        $table = $this->table('custom_fields_types');
        $table->insert($data)->save();
    }
}
