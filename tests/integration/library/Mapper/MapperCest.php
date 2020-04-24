<?php

namespace Gewaer\Tests\integration\library\Mapper;

use Canvas\Mapper\CompaniesGroupsMapper;
use Canvas\Mapper\CustomFieldsMapper;
use Canvas\Mapper\CustomFilterMapper;
use Canvas\Mapper\FileMapper;
use Canvas\Mapper\ListSchemaMapper;
use Canvas\Mapper\NotificationMapper;
use Canvas\Mapper\UserMapper;
use IntegrationTester;

class MapperCest
{
    public function kanvasCoreDefaultMapper(IntegrationTester $I)
    {
        $I->assertTrue(is_object(new CompaniesGroupsMapper()));
        $I->assertTrue(is_object(new CustomFieldsMapper()));
        $I->assertTrue(is_object(new CustomFilterMapper()));
        $I->assertTrue(is_object(new FileMapper(1, 2)));
        $I->assertTrue(is_object(new ListSchemaMapper()));
        $I->assertTrue(is_object(new NotificationMapper()));
        $I->assertTrue(is_object(new UserMapper()));
    }
}
