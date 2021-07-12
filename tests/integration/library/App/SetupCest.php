<?php

declare(strict_types=1);

namespace Canvas\Tests\integration\library\App;

use Canvas\App\Setup;
use Canvas\Models\AccessList;
use Canvas\Models\Apps;
use Canvas\Models\AppsPlans;
use Canvas\Models\AppsSettings;
use Canvas\Models\Menus;
use Canvas\Models\Resources;
use Canvas\Models\Roles;
use Canvas\Models\SystemModules;
use IntegrationTester;
use Phalcon\Security\Random;

class SetupCest
{
    private $app;

    /**
     * Constructor.
     *
     * @return void
     */
    public function onContruct()
    {
        $random = new Random();
        $app = new Apps();
        $name = 'TestApp-' . $random->base58();
        $app->name = $name;
        $app->description = $name;
        $app->ecosystem_auth = 1;
        $app->url = 'http://' . strtolower($name) . '.com';
        $app->default_apps_plan_id = 1;
        $app->is_actived = 1;
        $app->payments_active = 1;
        $app->is_public = 1;
        $app->saveOrFail();

        $this->app = $app;
    }

    /**
     * Create default settings for new app.
     *
     * @param IntegrationTester $I
     *
     * @return void
     */
    public function createDefaultSettings(IntegrationTester $I)
    {
        $setup = new Setup($this->app);
        $setup->settings();
        $appSettings = AppsSettings::findOrFail($this->app->getId());
        $I->assertTrue(count($appSettings) == AppsSettings::APP_DEFAULT_SETTINGS_NUMBER);
    }

    /**
     * Create default app plans for new app.
     *
     * @param IntegrationTester $I
     *
     * @return void
     */
    public function createDefaultPlans(IntegrationTester $I)
    {
        $appPlans = AppsPlans::findOrFail([
            'conditions' => 'apps_id = :apps_id: and is_deleted = 0',
            'bind' => ['apps_id' => $this->app->getId()]
        ]);

        $I->assertTrue(count($appPlans) == 2);
    }

    /**
     * Add Acl settings for new app.
     *
     * @param IntegrationTester $I
     *
     * @return void
     */
    public function createDefaultAcl(IntegrationTester $I)
    {
        $defaultCountAppRoleCount = 1;
        $defaultCountAppRolesEcosystem = 3;
        $defaultCountAppResources = 9;
        $defaultCountAccessList = 53;
        //check app roles
        $roles = Roles::findOrFail([
            'conditions' => 'apps_id = :apps_id: and is_deleted = 0',
            'bind' => [
                'apps_id' => $this->app->getId()
            ]
        ]);

        $I->assertTrue(count($roles) == $defaultCountAppRoleCount);

        //Check global roles
        $roles = Roles::findOrFail([
            'conditions' => 'apps_id = :apps_id: and is_deleted = 0',
            'bind' => [
                'apps_id' => Apps::CANVAS_DEFAULT_APP_ID
            ]
        ]);

        $I->assertTrue(count($roles) == $defaultCountAppRolesEcosystem);

        //Check number of resources
        $resources = Resources::findOrFail([
            'conditions' => 'apps_id = :apps_id: and is_deleted = 0',
            'bind' => ['apps_id' => $this->app->getId()]
        ]);

        $I->assertTrue(count($resources) == $defaultCountAppResources);

        //Check Access List privileges
        $accessList = AccessList::findOrFail([
            'conditions' => 'apps_id = :apps_id: and is_deleted = 0',
            'bind' => ['apps_id' => $this->app->getId()]
        ]);

        $I->assertTrue(count($accessList) == $defaultCountAccessList);
    }

    /**
     * Create default system modules for new app.
     *
     * @param IntegrationTester $I
     *
     * @return void
     */
    public function createDefaultSystemModules(IntegrationTester $I)
    {
        $systemModules = SystemModules::findOrFail([
            'conditions' => 'apps_id = :apps_id: and is_deleted = 0',
            'bind' => ['apps_id' => $this->app->getId()]
        ]);

        $I->assertTrue(count($systemModules) == 3);
    }

    /**
     * Create default email templates for new app.
     *
     * @param IntegrationTester $I
     *
     * @return void
     */
    public function createDefaultEmailTemplates(IntegrationTester $I)
    {
        $emailTemplates = SystemModules::findOrFail([
            'conditions' => 'apps_id = :apps_id: and is_deleted = 0',
            'bind' => ['apps_id' => $this->app->getId()]
        ]);

        $I->assertTrue(count($emailTemplates) == 3);
    }

    /**
     * Create default sidebar menus for new app.
     *
     * @param IntegrationTester $I
     *
     * @return void
     */
    public function createDefaultMenus(IntegrationTester $I)
    {
        $menu = Menus::findFirstOrFail([
            'conditions' => 'apps_id = :apps_id: and is_deleted = 0',
            'bind' => ['apps_id' => $this->app->getId()]
        ]);

        $I->assertTrue(count($menu->getLinks()) == 3);
    }
}
