<?php

namespace Gewaer\Tests\integration\library\Models;

use Canvas\Models\AccessList;
use Canvas\Models\Apps;
use Canvas\Models\Roles;
use IntegrationTester;
use Canvas\Providers\ConfigProvider;
use Phalcon\Di\FactoryDefault;

class AccessListCest
{
    public function validateRelationships(IntegrationTester $I)
    {
        $actual = $I->getModelRelationships(AccessList::class);
        $expected = [
            [0, 'roles_name', Roles::class, 'name', ['alias' => 'role']],
        ];

        $I->assertEquals($expected, $actual);
    }

    public function validateExist(IntegrationTester $I)
    {
        $acceList = AccessList::findFirst();
        $I->assertTrue((bool) AccessList::exist($acceList->role, $acceList->resources_name, $acceList->access_name));
    }

    public function validateGetBy(IntegrationTester $I)
    {
        $acceList = AccessList::findFirst();
        $I->assertTrue(AccessList::getBy($acceList->role, $acceList->resources_name, $acceList->access_name) instanceof AccessList);
    }
}
