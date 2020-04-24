<?php

namespace Gewaer\Tests\integration\library\Models;

use Canvas\Models\Apps;
use Canvas\Models\AppsSettings;
use IntegrationTester;

class AppsSettingsCest
{
    public function validateRelationships(IntegrationTester $I)
    {
        $actual = $I->getModelRelationships(AppsSettings::class);
        $expected = [
            [0, 'apps_id', Apps::class, 'id', ['alias' => 'app']]
        ];

        $I->assertEquals($expected, $actual);
    }

    public function validateConstants(IntegrationTester $I)
    {
        $I->assertTrue(AppsSettings::APP_DEFAULT_SETTINGS_NUMBER > 0);
        $I->assertTrue(AppsSettings::SUBSCRIPTION_BASED == 'subscription_based');
    }
}
