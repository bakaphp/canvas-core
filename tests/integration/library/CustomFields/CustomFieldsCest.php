<?php

namespace Gewaer\Tests\integration\library\Jobs;

use Canvas\CustomFields\CustomFields;
use Canvas\Models\SystemModules;
use IntegrationTester;

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

    public function appCustomFields(IntegrationTester $I)
    {
        $value = $I->faker()->name;

        $systemModule = SystemModules::findFirst();
        $systemModule->set('test', $value);

        $I->assertTrue($systemModule->get('test') == $value);
    }
}
