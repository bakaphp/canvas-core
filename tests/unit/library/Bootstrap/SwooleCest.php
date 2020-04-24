<?php

namespace Canvas\Tests\unit\library\Bootstrap;

use Canvas\Bootstrap\AbstractBootstrap;
use Canvas\Bootstrap\Swoole;
use UnitTester;

class SwooleCest
{
    public function checkBootstrap(UnitTester $I)
    {
        $bootstrap = new Swoole();
        $I->assertTrue($bootstrap instanceof AbstractBootstrap);
    }
}
