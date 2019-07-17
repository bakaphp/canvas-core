<?php

namespace Gewaer\Tests\integration\library\Models;

use Canvas\Models\Apps;
use IntegrationTester;
use Canvas\Providers\ConfigProvider;
use Phalcon\Di\FactoryDefault;

class AppsCest
{
    /**
     * Confirm the default apps exist.
     *
     * @param IntegrationTester $I
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
     * @return void
     */
    public function getGewaerApp(IntegrationTester $I)
    {
        $app = Apps::getACLApp('Gewaer');
        $I->assertTrue($app->key == $I->grabFromDi('config')->app->id);
    }

    /**
     * Validate is an app has an active status or not
     *
     * @param UnitTester $I
     * @return void
     */
    public function isActive(IntegrationTester $I)
    {
        $app = Apps::getACLApp('Default');
        $I->assertTrue(gettype($app->isActive()) == 'boolean');
    }
}
