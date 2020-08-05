<?php

namespace Canvas\Tests\unit\library\Constants;

use Canvas\Constants\Flags;
use UnitTester;

class FlagsCest
{
    public function checkConstants(UnitTester $I)
    {
        $I->assertEquals(1, Flags::ACTIVE);
        $I->assertEquals(2, Flags::INACTIVE);
        $I->assertEquals('production', Flags::PRODUCTION);
        $I->assertEquals('development', Flags::DEVELOPMENT);
    }
}
