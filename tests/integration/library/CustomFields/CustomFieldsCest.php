<?php

namespace Gewaer\Tests\integration\library\Jobs;

use Canvas\CustomFields\CustomFields;
use IntegrationTester;
use UnitTester;

class CustomFieldsCest
{
    public function addValues(IntegrationTester $I)
    {
        $customFields = CustomFields::findFirst();

        $I->assertTrue($customFields->addValues([
            'test_field' => 'testing',
            'test_field_2' => 'testing'
        ]));
    }
}
