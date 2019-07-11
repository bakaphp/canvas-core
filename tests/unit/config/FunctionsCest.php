<?php

namespace Canvas\Tests\unit\config;

use UnitTester;
use Codeception\PHPUnit\TestCase;
use function Canvas\Core\appPath;
use function Canvas\Core\envValue;
use function Canvas\Core\isJson;
use function Canvas\Core\appUrl;
use function Canvas\Core\paymentGatewayIsActive;

class FunctionsCest
{
    // AppPath
    public function checkApppath(UnitTester $I)
    {
        $path = dirname(dirname(dirname(__DIR__)));
        $I->assertEquals($path . '/', appPath());
    }

    public function checkApppathWithParameter(UnitTester $I)
    {
        $path = dirname(dirname(dirname(__DIR__))) . '/library/Core/config.php';
        $I->assertEquals($path, appPath('library/Core/config.php'));
    }

    //EnvValue
    public function checkEnvvalueAsFalse(UnitTester $I)
    {
        putenv('SOMEVAL=false');
        $I->assertFalse(envValue('SOMEVAL'));
    }

    public function checkEnvvalueAsTrue(UnitTester $I)
    {
        putenv('SOMEVAL=true');
        $I->assertTrue(envValue('SOMEVAL'));
    }

    public function checkEnvvalueWithValue(UnitTester $I)
    {
        putenv('SOMEVAL=someval');
        $I->assertEquals('someval', envValue('SOMEVAL'));
    }


    //AppUrl
    public function checkAppUrlTrue(UnitTester $I)
    {
        $I->assertNotEmpty(appUrl('users',1));
    }

    //IsJson
    public function checkIsJsonTrue(UnitTester $I)
    {
        $array = ['name'=>'example'];
        $I->assertTrue(isJson(json_encode($array)));
    }

    public function checkIsJsonFalse(UnitTester $I)
    {
        $string = 'example';
        $I->assertFalse(isJson($string));
    }

    //PaymentGatewayIsActive
    public function checkPaymentGatewayIsActiveTrue(UnitTester $I)
    {
        $I->assertTrue(paymentGatewayIsActive());
    }
}
