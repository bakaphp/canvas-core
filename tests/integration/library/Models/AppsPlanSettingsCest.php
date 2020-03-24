<?php

namespace Gewaer\Tests\integration\library\Models;

use Canvas\Models\Apps;
use Canvas\Models\PaymentFrequencies;
use Canvas\Models\AppsPlansSettings;
use IntegrationTester;

class AppsPlansSettingsCest
{
    public function validateRelationships(IntegrationTester $I)
    {
        $actual = $I->getModelRelationships(AppsPlansSettings::class);
        $expected = [
            [2, 'apps_id', AppsPlansSettings::class, 'apps_id', ['alias' => 'settings']],
            [1, 'payment_frequencies_id', PaymentFrequencies::class, 'id', ['alias' => 'paymentFrequecies']],
            [0, 'apps_id', Apps::class, 'id', ['alias' => 'app']]
        ];

        $I->assertEquals($expected, $actual);
    }
}
