<?php

namespace Canvas\Tests\unit\library\Bootstrap;

use Canvas\Bootstrap\AbstractBootstrap;
use Canvas\Bootstrap\Cli;
use UnitTester;

class CliCest
{
    public function checkBootstrap(UnitTester $I)
    {
        $bootstrap = new Cli();
        $I->assertTrue($bootstrap instanceof AbstractBootstrap);
    }
}
