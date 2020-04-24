<?php

namespace Gewaer\Tests\integration\library\Models;

use Baka\Auth\Models\Companies as ModelsCompanies;
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
            [0, 'company_id', ModelsCompanies::class, 'id', ['alias' => 'company']],
            [0, 'companies_id', Companies::class, 'id', ['alias' => 'company']]
        ];
        $I->assertEquals($expected, $actual);
    }
}
