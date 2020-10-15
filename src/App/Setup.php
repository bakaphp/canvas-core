<?php

declare(strict_types=1);

namespace Canvas\App;

use Canvas\Models\Apps;
use Canvas\Models\AppsPlans;
use Canvas\Models\Companies;
use Canvas\Models\Roles;
use Canvas\Models\SystemModules;
use Canvas\Models\Users;
use Phalcon\Di;

class Setup
{
    protected Apps $app;

    /**
     * Construct.
     *
     * @param Apps $app
     */
    public function __construct(Apps $app)
    {
        $this->app = $app;
    }

    /**
     * Set default settings.
     */
    public function settings() : self
    {
        if (!$this->app->hasSettings()) {
            foreach ($this->SettingsData() as $key => $value) {
                $this->app->set($value['name'], $value['value']);
            }
        }

        return $this;
    }

    /**
     * Set default plans.
     */
    public function plans() : self
    {
        foreach ($this->plansData() as $plan) {
            $appPlans = new AppsPlans();
            $appPlans->assign($plan);
            $appPlans->apps_id = $this->app->getId();
            $appPlans->saveOrFail();
        }

        return $this;
    }

    /**
     * Set the system modules.
     *
     * @return self
     */
    public function systemModules() : self
    {
        foreach ($this->systemModulesData() as $module) {
            $systemModule = new SystemModules();
            $systemModule->assign($module);
            $systemModule->apps_id = $this->app->getId();
            $systemModule->saveOrFail();
        }

        return $this;
    }

    /**
     * Default settings.
     *
     * @return array
     */
    public function SettingsData() : array
    {
        return [
            [
                'name' => 'language',
                'value' => 'EN',
            ], [
                'name' => 'timezone',
                'value' => 'America/New_York',
            ], [
                'name' => 'currency',
                'value' => 'USD',
            ], [
                'name' => 'filesystem',
                'value' => 's3',
            ], [
                'name' => 'allow_user_registration',
                'value' => '1',
            ], [
                'name' => 'background_image',
                'value' => getenv('FILESYSTEM_CDN_URL') . '/default-background-auth.jpg',
            ], [
                'name' => 'logo',
                'value' => getenv('FILESYSTEM_CDN_URL') . '/gewaer-logo-dark.png',
            ], [
                'name' => 'registered',
                'value' => 1,
            ], [
                'name' => 'favicon',
                'value' => getenv('FILESYSTEM_CDN_URL') . '/gewaer-logo-dark.png',
            ], [
                'name' => 'titles',
                'value' => $this->app->name,
            ], [
                'name' => 'base_color',
                'value' => '#61c2cc',
            ], [
                'name' => 'secondary_color',
                'value' => '#9ee5b5',
            ], [
                'name' => 'allow_social_auth',
                'value' => '1',
            ], [
                'name' => 'allowed_social_auths',
                'value' => '{"google": 1,"facebook": 0,"github": 0,"apple": 0}',
            ], [
                'name' => 'default_sidebar_state',
                'value' => 'closed',
            ], [
                'name' => 'show_notifications',
                'value' => '1',
            ], [
                'name' => 'delete_images_on_empty_files_field',
                'value' => '1',
            ], [
                'name' => 'public_images',
                'value' => '0',
            ]
        ];
    }

    /**
     * Default app plans.
     *
     * @return array
     */
    public function plansData() : array
    {
        return [
            [
                'name' => 'monthly-10-1',
                'payment_interval' => 'monthly',
                'description' => 'monthly-10-1',
                'stripe_id' => 'monthly-10-1',
                'stripe_plan' => 'monthly-10-1',
                'pricing' => 10,
                'pricing_annual' => 100,
                'currency_id' => 1,
                'free_trial_dates' => 14,
                'is_default' => 1,
                'payment_frequencies_id' => 1
            ], [
                'apps_id' => '1',
                'name' => 'yearly-10-1',
                'payment_interval' => 'yearly',
                'description' => 'yearly-10-1',
                'stripe_id' => 'yearly-10-1',
                'stripe_plan' => 'yearly-10-1',
                'pricing' => 100,
                'pricing_annual' => 60,
                'currency_id' => 1,
                'free_trial_dates' => 14,
                'is_default' => 0,
                'payment_frequencies_id' => 2
            ]
        ];
    }

    /**
     *  Set default ACL data for this app.
     *
     * @return void
     */
    public function acl() : self
    {
        $acl = Di::getDefault()->get('acl');
        $acl->setApp($this->app);

        $acl->addRole($this->app->name . '.Admins');
        $acl->addRole($this->app->name . '.Users');

        $acl->addComponent(
            $this->app->name . '.Users',
            [
                'read',
                'list',
                'create',
                'update',
                'delete'
            ]
        );

        $acl->allow(
            'Admins',
            $this->app->name . '.Users',
            [
                'read',
                'list',
                'create',
                'update',
                'delete'
            ]
        );

        $acl->addComponent(
            $this->app->name . '.SettingsMenu',
            [
                'company-settings',
                'app-settings',
                'companies-manager',
            ]
        );

        $defaultResources = [
            $this->app->name . '.CompanyBranches',
            $this->app->name . '.CompanyUsers',
            $this->app->name . '.CompanyRoles',
            $this->app->name . '.CompanySubscriptions',
            $this->app->name . '.CustomFields',
            $this->app->name . '.CompaniesManager',
            $this->app->name . '.Apps-plans'
        ];

        foreach ($defaultResources as $resource) {
            $acl->addComponent(
                $resource,
                [
                    'read',
                    'list',
                    'create',
                    'update',
                    'delete'
                ]
            );

            $acl->allow(
                'Admins',
                $resource,
                [
                    'read',
                    'list',
                    'create',
                    'update',
                    'delete'
                ]
            );
        }

        $acl->allow(
            'Admins',
            $this->app->name . '.SettingsMenu',
            [
                'company-settings',
                'app-settings',
                'companies-manager',
            ]
        );

        return $this;
    }

    /**
     * Default system modules.
     *
     * @return array
     */
    public function systemModulesData() : array
    {
        return [
            [
                'name' => 'Companies',
                'slug' => 'companies',
                'model_name' => Companies::class,
                'parents_id' => '0',
                'menu_order' => null,
                'show' => '1',
                'use_elastic' => '0',
                'browse_fields' => '[{"name":"name","title":"Name","sortField":"name","filterable":true,"searchable":true},{"name":"address","title":"Address","sortField":"address","filterable":true,"searchable":true},{"name":"timezone","title":"Timezone","sortField":"timezone","filterable":true,"searchable":true},{"name":"website","title":"Website","sortField":"website","filterable":true,"searchable":true}]',
                'mobile_component_type' => null,
                'mobile_navigation_type' => null,
                'mobile_tab_index' => '0'
            ],
            [
                'name' => 'Users',
                'slug' => 'users',
                'model_name' => Users::class,
                'parents_id' => '0',
                'menu_order' => null,
                'show' => '1',
                'use_elastic' => '0',
                'browse_fields' => '[{"name":"firstname","title":"First Name","sortField":"firstname","filterable":true,"searchable":true},{"name":"lastname","title":"Last Name","sortField":"lastname","filterable":true,"searchable":true},{"name":"email","title":"Email","sortField":"email","filterable":true,"searchable":true},{"name":"displayname","title":"Display Name","sortField":"displayname","filterable":true,"searchable":true}]',
                'mobile_component_type' => null,
                'mobile_navigation_type' => null,
                'mobile_tab_index' => '0'
            ],
            [
                'name' => 'Roles',
                'slug' => 'roles',
                'model_name' => Roles::class,
                'apps_id' => '1',
                'parents_id' => '0',
                'menu_order' => null,
                'show' => '0',
                'use_elastic' => '0',
                'browse_fields' => '[{"name":"name","title":"Name","sortField":"name","filterable":true,"searchable":true},{"name":"description","title":"Description"}]',
                'mobile_component_type' => null,
                'mobile_navigation_type' => null,
                'mobile_tab_index' => '0'
            ],
        ];
    }
}
