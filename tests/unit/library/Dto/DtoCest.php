<?php

namespace Canvas\Tests\unit\library\Dto;

use Canvas\Dto\AppsSettings;
use Canvas\Dto\CompaniesGroups;
use Canvas\Dto\CustomFields;
use Canvas\Dto\CustomFilter;
use Canvas\Dto\Files;
use Canvas\Dto\ListSchema;
use Canvas\Dto\Notification;
use Canvas\Dto\User;
use UnitTester;

class DtoCest
{
    public function checkCanvasDefault(UnitTester $I)
    {
        $I->assertTrue(is_object(new AppsSettings));
        $I->assertTrue(is_object(new CompaniesGroups));
        $I->assertTrue(is_object(new CustomFields));
        $I->assertTrue(is_object(new CustomFilter));
        $I->assertTrue(is_object(new Files));
        $I->assertTrue(is_object(new ListSchema));
        $I->assertTrue(is_object(new Notification));
        $I->assertTrue(is_object(new User));
    }

}
