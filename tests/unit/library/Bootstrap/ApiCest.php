<?php

namespace Canvas\Tests\unit\library\Bootstrap;

use Canvas\Bootstrap\AbstractBootstrap;
use Canvas\Bootstrap\Api;
use UnitTester;

class ApiCest
{
    public function checkBootstrap(UnitTester $I)
    {
        $bootstrap = new Api();
        $I->assertTrue($bootstrap instanceof AbstractBootstrap);
    }
}
