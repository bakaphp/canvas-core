<?php

namespace Gewaer\Tests\integration\library\Jobs;

use Canvas\Models\Apps;
use Canvas\Models\AppsSettings;
use Canvas\Models\AppsPlans;
use Canvas\Models\SystemModules;
use Canvas\Models\Menus;
use Canvas\Models\MenusLinks;
use Exception;
use IntegrationTester;
use Page\Data;
use Phalcon\Security\Random;
use Canvas\App\Setup;

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
        $app->name = 'TestApp-' . $random->base58();
        $app->description = $name;
        $app->ecosystem_auth = 1;
        $app->url = '';
        $app->default_apps_plan_id = 1;
        $app->is_actived = 1;
        $app->payments_active = 1;
        $app->is_public = 1;
        $app->saveOrFail();

        $this->app = $app;
    }

    /**
     * Create default settings for new app
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
     * Create default app plans for new app
     *
     * @param IntegrationTester $I
     *
     * @return void
     */
    public function createDefaultPlans(IntegrationTester $I)
    {
        $setup = new Setup($this->app);
        $setup->plans();
        $appPlans = AppsPlans::findOrFail([
            "conditions" => "apps_id = :apps_id: and is_deleted = 0",
            "bind" => ["apps_id" => $this->app->getId()]
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
        $setup = new Setup($this->app);
        $setup->acl();
        $I->assertTrue($setup->acl() instanceof Setup);
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
        $setup = new Setup($this->app);
        $setup->systemModules();
        $systemModules = SystemModules::findOrFail([
            "conditions" => "apps_id = :apps_id: and is_deleted = 0",
            "bind" => ["apps_id" => $this->app->getId()]
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
        $setup = new Setup($this->app);
        $setup->emailTemplates();
        $emailTemplates = SystemModules::findOrFail([
            "conditions" => "apps_id = :apps_id: and is_deleted = 0",
            "bind" => ["apps_id" => $this->app->getId()]
        ]);
        
        $I->assertTrue(count($emailTemplates) == 3);
    }

    /**
     * Create default sidebar menus for new app
     *
     * @param IntegrationTester $I
     *
     * @return void
     */
    public function createDefaultMenus(IntegrationTester $I)
    {
        $setup = new Setup($this->app);
        $setup->defaultMenus();

        $menu = Menus::findFirstOrFail([
            "conditions" => "apps_id = :apps_id: and is_deleted = 0",
            "bind" => ["apps_id" => $this->app->getId()]
        ]);

        $I->assertTrue(count($menu->getLinks()) == 3);
    }
}
