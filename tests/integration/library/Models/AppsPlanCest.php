<?php

namespace Gewaer\Tests\integration\library\Models;

use Canvas\Models\Apps;
use Canvas\Models\AppsPlans;
use Canvas\Models\AppsPlansSettings;
use IntegrationTester;
use Canvas\Providers\ConfigProvider;
use Phalcon\Di\FactoryDefault;

class AppsPlansCest
{
    /**
     * Confirm the default apps exist.
     *
     * @param IntegrationTester $I
     * @return void
     */
    public function getDefaultPlan(IntegrationTester $I)
    {
        $I->assertTrue(AppsPlans::getDefaultPlan() instanceof AppsPlans);
    }

    /**
     * Confirm the default apps exist.
     *
     * @param IntegrationTester $I
     * @return void
     */
    public function get(IntegrationTester $I)
    {
        $appPlan = AppsPlans::findFirst(1);
        $I->assertTrue(gettype($appPlan->get('example123456')) == 'string');
    }
}
