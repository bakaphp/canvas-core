<?php

namespace Canvas\Tests\unit\config;

use Canvas\Providers\CliDispatcherProvider;
use Canvas\Providers\ConfigProvider;
use Canvas\Providers\DatabaseProvider;
use Canvas\Providers\ErrorHandlerProvider;
use Canvas\Providers\LoggerProvider;
use Canvas\Providers\ModelsMetadataProvider;
use Canvas\Providers\RequestProvider;
use Canvas\Providers\RouterProvider;
use UnitTester;
use function Canvas\Core\appPath;

class ProvidersCest
{
    public function checkApiProviders(UnitTester $I)
    {
        $providers = require appPath('tests/providers.php');

        $I->assertEquals(ConfigProvider::class, $providers[0]);
        $I->assertEquals(LoggerProvider::class, $providers[1]);
        $I->assertEquals(ErrorHandlerProvider::class, $providers[2]);
        $I->assertEquals(DatabaseProvider::class, $providers[3]);
        $I->assertEquals(ModelsMetadataProvider::class, $providers[4]);
        $I->assertEquals(RequestProvider::class, $providers[5]);
    }

    public function checkCliProviders(UnitTester $I)
    {
        $providers = require appPath('tests/providers.php');

        $I->assertEquals(ConfigProvider::class, $providers[0]);
        $I->assertEquals(LoggerProvider::class, $providers[1]);
        $I->assertEquals(ErrorHandlerProvider::class, $providers[2]);
        $I->assertEquals(DatabaseProvider::class, $providers[3]);
        $I->assertEquals(ModelsMetadataProvider::class, $providers[4]);
    }
}
