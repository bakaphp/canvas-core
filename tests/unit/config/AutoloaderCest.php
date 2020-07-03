<?php

namespace Canvas\Tests\unit\config;

use Canvas\Http\Response;
use UnitTester;
use function function_exists;
use function Baka\appPath;

class AutoloaderCest
{
    public function checkDotenvVariables(UnitTester $I)
    {
        require appPath('tests/autoload.php');

        $I->assertNotEquals(false, getenv('APP_DEBUG'));
        $I->assertNotEquals(false, getenv('APP_ENV'));
        $I->assertNotEquals(false, getenv('APP_URL'));
        $I->assertNotEquals(false, getenv('APP_NAME'));
        $I->assertNotEquals(false, getenv('APP_BASE_URI'));
        $I->assertNotEquals(false, getenv('APP_SUPPORT_EMAIL'));
        $I->assertNotEquals(false, getenv('APP_TIMEZONE'));
        $I->assertNotEquals(false, getenv('CACHE_PREFIX'));
        $I->assertNotEquals(false, getenv('CACHE_LIFETIME'));
        $I->assertNotEquals(false, getenv('DATA_API_MYSQL_NAME'));
        $I->assertNotEquals(false, getenv('LOGGER_DEFAULT_FILENAME'));
        $I->assertNotEquals(false, getenv('VERSION'));

        $I->assertEquals('true', getenv('APP_DEBUG'));
        $I->assertEquals('development', getenv('APP_ENV'));
        $I->assertEquals('/', getenv('APP_BASE_URI'));
        $I->assertEquals('UTC', getenv('APP_TIMEZONE'));
        $I->assertEquals('api_cache_', getenv('CACHE_PREFIX'));
        $I->assertEquals(86400, getenv('CACHE_LIFETIME'));
        $I->assertEquals('api', getenv('LOGGER_DEFAULT_FILENAME'));
        $I->assertEquals('20180401', getenv('VERSION'));
    }

    public function checkAutoloader(UnitTester $I)
    {
        require appPath('tests/autoload.php');

        $class = new Response();
        $I->assertTrue($class instanceof Response);
        $I->assertTrue(function_exists('Baka\envValue'));
    }
}
