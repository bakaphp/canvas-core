<?php

namespace Gewaer\Tests\integration\library\Models;

use Canvas\Models\Apps;
use IntegrationTester;
use Canvas\Providers\ConfigProvider;
use Phalcon\Di\FactoryDefault;

class AppsCest
{
    /**
     * Confirm the default apps exist
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
     * Confirm the default apps exist
     *
     * @param IntegrationTester $I
     * @return void
     */
    public function getGewaerApp(IntegrationTester $I)
    {
        $diContainer = new FactoryDefault();

        $provider = new ConfigProvider();
        $provider->register($diContainer);

        $app = Apps::getACLApp('Gewaer');
        $I->assertTrue($app->getId() == $diContainer->getShared('config')->app->id);
    }
}
