<?php

namespace Gewaer\Tests\integration\library\Models;

use Canvas\Models\Apps;
use Canvas\Models\AppsPlans;
use Canvas\Models\PaymentFrequencies;
use Canvas\Models\AppsPlansSettings;
use IntegrationTester;

class AppsPlansCest
{
    public function validateRelationships(IntegrationTester $I)
    {
        $actual = $I->getModelRelationships(AppsPlans::class);
        $expected = [
            [0, 'apps_id', Apps::class, 'id', ['alias' => 'app']],
            [0, 'payment_frequencies_id', PaymentFrequencies::class, 'id', ['alias' => 'paymentFrequencies']],
            [0, 'payment_frequencies_id', PaymentFrequencies::class, 'id', ['alias' => 'paymentFrequecies']],
            [2, 'apps_id', AppsPlansSettings::class, 'apps_id', ['alias' => 'settings']]
        ];

        $I->assertEquals($expected, $actual);
    }

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
        $I->assertTrue(gettype($appPlan->get('users_total')) == 'string');
    }
}
