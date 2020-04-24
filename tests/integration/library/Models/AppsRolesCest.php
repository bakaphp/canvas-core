<?php

namespace Gewaer\Tests\integration\library\Models;

use Baka\Auth\Models\Apps as ModelsApps;
use Canvas\Models\Apps;
use Canvas\Models\AppsRoles;
use IntegrationTester;

class AppsRolesCest
{
    public function validateRelationships(IntegrationTester $I)
    {
        $actual = $I->getModelRelationships(AppsRoles::class);
        $expected = [
            [0, 'apps_id', ModelsApps::class, 'id', ['alias' => 'app']],
            [0, 'apps_id', Apps::class, 'id', ['alias' => 'app']]
        ];

        $I->assertEquals($expected, $actual);
    }
}
