<?php

namespace Canvas\Cli\Tasks;

use Phalcon\Cli\Task as PhTask;

/**
 * Class AclTask.
 *
 * @package Canvas\Cli\Tasks;
 *
 * @property \Canvas\Acl\Manager $acl
 */
class AclTask extends PhTask
{
    /**
     * Create the default roles of the system.
     *
     * @return void
     */
    public function mainAction()
    {
        $this->acl->addRole('Default.Admins');
        $this->acl->addRole('Default.Agents');
        $this->acl->addRole('Default.Users');

        $this->acl->addResource('Default.Users', ['read', 'list', 'create', 'update', 'delete']);
        $this->acl->allow('Admins', 'Default.Users', ['read', 'list', 'create', 'update', 'delete']);

        $this->kanvas();
    }

    /**
     * Default roles for the crm system.
     *
     * @return void
     */
    public function crmAction()
    {
        $this->acl->addRole('CRM.Users');
        $this->acl->addResource('CRM.Users', ['read', 'list', 'create', 'update', 'delete']);
        $this->acl->allow('Users', 'CRM.Users', ['read', 'list', 'create']);
        $this->acl->deny('Users', 'CRM.Users', ['update', 'delete']);

        //Apps Settings
        $this->acl->addResource('CRM.AppsSettings', ['read', 'list', 'create', 'update', 'delete']);
        $this->acl->allow('Users', 'CRM.AppsSettings', ['read', 'list', 'create', 'update', 'delete']);

        //Companies Settings
        $this->acl->addResource('CRM.CompaniesSettings', ['read', 'list', 'create', 'update', 'delete']);
        $this->acl->allow('Users', 'CRM.CompaniesSettings', ['read', 'list', 'create', 'update', 'delete']);

        //Apps plans
        $this->acl->addResource('CRM.Apps-plans', ['read', 'list', 'create', 'update', 'delete']);
        $this->acl->allow('Users', 'CRM.Apps-plans', ['read', 'list', 'create', 'update', 'delete']);
    }

    /**
     * Default ecosystem ACL.
     *
     * @return void
     */
    public function kanvas(): void
    {
        $this->acl->addResource(
            'Default.SettingsMenu',
            [
                'company-settings',
                'app-settings',
                'companies-manager',
            ]
        );

        $defaultResources = [
            'Default.CompanyBranches',
            'Default.CompanyUsers',
            'Default.CompanyRoles',
            'Default.CompanySubscriptions',
            'Default.CustomFields',
            'Default.CompaniesManager',
            'Default.Apps-plans'
        ];

        foreach ($defaultResources as $resource) {
            $this->acl->addResource(
                $resource,
                [
                    'read',
                    'list',
                    'create',
                    'update',
                    'delete'
                ]
            );

            $this->acl->allow(
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

        $this->acl->allow(
            'Admins',
            'Default.SettingsMenu',
            [
                'company-settings',
                'app-settings',
                'companies-manager',
            ]
        );
    }
}
