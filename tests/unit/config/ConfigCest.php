<?php

namespace Canvas\Tests\unit\config;

use UnitTester;
use function is_array;
use function Canvas\Core\appPath;

class ConfigCest
{
    public function checkConfig(UnitTester $I)
    {
        $config = require appPath('tests/config.php');

        $I->assertTrue(is_array($config));
        $I->assertTrue(isset($config['app']));
        $I->assertTrue(isset($config['cache']));
    }
}
