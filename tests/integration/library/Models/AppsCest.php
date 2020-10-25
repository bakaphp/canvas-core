<?php

namespace Gewaer\Tests\integration\library\Models;

use Canvas\Models\Apps;
use Canvas\Models\AppsPlans;
use Canvas\Models\AppsSettings;
use Canvas\Models\UserWebhooks;
use IntegrationTester;

class AppsCest
{
    public function validateRelationships(IntegrationTester $I)
    {
        $actual = $I->getModelRelationships(Apps::class);
        $expected = [
            [2, 'id', AppsPlans::class, 'apps_id', ['alias' => 'plans']],
            [2, 'id', UserWebhooks::class, 'apps_id', ['alias' => 'user-webhooks']],
            [2, 'id', AppsSettings::class, 'apps_id', ['alias' => 'settingsApp']],
            [1, 'default_apps_plan_id', AppsPlans::class, 'id', ['alias' => 'plan']],
        ];

        $I->assertEquals($expected, $actual);
    }

    /**
     * Confirm the default apps exist.
     *
     * @param IntegrationTester $I
     *
     * @return void
     */
    public function getDefaultApp(IntegrationTester $I)
    {
        $app = Apps::getACLApp(Apps::CANVAS_DEFAULT_APP_NAME);
        $I->assertTrue($app->name == Apps::CANVAS_DEFAULT_APP_NAME);
    }

    /**
     * Confirm the default apps exist.
     *
     * @param IntegrationTester $I
     *
     * @return void
     */
    public function getGewaerApp(IntegrationTester $I)
    {
        $app = Apps::getACLApp('Gewaer');
        $I->assertTrue($app->key == $I->grabFromDi('config')->app->id);
    }

    /**
     * Validate is an app has an active status or not.
     *
     * @param UnitTester $I
     *
     * @return void
     */
    public function isActive(IntegrationTester $I)
    {
        $app = Apps::getACLApp('Default');
        $I->assertTrue(gettype($app->isActive()) == 'boolean');
    }

    public function validateEcosystemAuth(IntegrationTester $I)
    {
        $app = Apps::getACLApp('Default');
        $I->assertTrue(is_bool($app->ecosystemAuth()));
    }

    public function validateSubscriptionBased(IntegrationTester $I)
    {
        $app = Apps::getACLApp('Default');
        $I->assertTrue(is_bool($app->subscriptionBased()));
    }

    public function getAppByDomain(IntegrationTester $I)
    {
        $app = Apps::findFirst(1);
        $app->domain = 'localhost';
        $app->domain_based = 1;
        $app->update();

        $I->assertTrue(Apps::getByDomainName('localhost') instanceof Apps);

        //revert
        $app = Apps::findFirst(1);
        $app->domain = '';
        $app->domain_based = 0;
        $app->update();
    }
}
