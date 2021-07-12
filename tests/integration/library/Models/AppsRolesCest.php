<?php

namespace Canvas\Tests\integration\library\Models;

use Canvas\Auth\Models\Apps as ModelsApps;
use Canvas\Models\Apps;
use Canvas\Models\AppsRoles;
use IntegrationTester;

class AppsRolesCest
{
    public function validateRelationships(IntegrationTester $I)
    {
        $actual = $I->getModelRelationships(AppsRoles::class);

        $expected = [
            [0, 'apps_id', Apps::class, 'id', ['alias' => 'app']],
            [0, 'apps_id', ModelsApps::class, 'id', ['alias' => 'appAuth']]
        ];

        $I->assertEquals($expected, $actual);
    }
}
