<?php

namespace Gewaer\Tests\unit;

use CliTester;
use Codeception\Util\HttpCode;
use function Canvas\Core\appPath;

class BootstrapCest
{
    public function checkBootstrap(CliTester $I)
    {
        ob_start();
        require appPath('/index.php');
        $actual = ob_get_contents();
        ob_end_clean();

        $results = json_decode($actual, true);
        $I->assertEquals('1.0', $results['jsonapi']['version']);
        $I->assertEquals(HttpCode::getDescription(404), $results['errors']['message']);
    }
}
