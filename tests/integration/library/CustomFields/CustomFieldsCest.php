<?php

namespace Canvas\Tests\integration\library\CustomFields;

use Canvas\CustomFields\CustomFields;
use Canvas\Models\AppsCustomFields;
use Canvas\Models\SystemModules;
use IntegrationTester;
use Phalcon\Mvc\Model\ResultsetInterface;

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

    public function findFirstByCustomField(IntegrationTester $I)
    {
        $searchCustomField = AppsCustomFields::findFirst();
        $customField = SystemModules::findFirstByCustomField($searchCustomField->name, $searchCustomField->value);

        $I->assertTrue($customField instanceof $searchCustomField->model_name);
    }

    public function findFirstByCustomFieldNotFound(IntegrationTester $I)
    {
        $value = $I->faker()->name;

        $customField = SystemModules::findFirstByCustomField('test', $value);

        $I->assertTrue($customField === null);
    }

    public function findByCustomField(IntegrationTester $I)
    {
        $searchCustomField = AppsCustomFields::findFirst();
        $customField = SystemModules::findByCustomField($searchCustomField->name, $searchCustomField->value);

        $I->assertTrue($customField instanceof ResultsetInterface);
        $I->assertTrue($customField->count() === 1);
        $I->assertTrue($customField->getFirst()->getEntity() instanceof $searchCustomField->model_name);
    }

    public function findByCustomFieldNoFound(IntegrationTester $I)
    {
        $value = $I->faker()->name;

        $customField = SystemModules::findByCustomField('test', $value);

        $I->assertTrue($customField instanceof ResultsetInterface);
        $I->assertTrue($customField->count() === 0);
    }
}
