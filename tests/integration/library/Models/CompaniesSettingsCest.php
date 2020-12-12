<?php

namespace Gewaer\Tests\integration\library\Models;

use Canvas\Models\AppsSettings;
use Canvas\Models\Companies;
use Canvas\Models\CompaniesSettings;
use IntegrationTester;

class CompaniesSettingsCest
{
    public function validateRelationships(IntegrationTester $I)
    {
        $actual = $I->getModelRelationships(CompaniesSettings::class);

        $expected = [
            [0, 'companies_id', Companies::class, 'id', ['alias' => 'company']]
        ];
        $I->assertEquals($expected, $actual);
    }
}
