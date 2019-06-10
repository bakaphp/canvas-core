<?php

namespace Canvas\Tests\unit\library;

use Canvas\ErrorHandler;
use Canvas\Logger;
use Canvas\Providers\ConfigProvider;
use Canvas\Providers\LoggerProvider;
use Phalcon\Config;
use Phalcon\Di\FactoryDefault;
use UnitTester;
use function Canvas\Core\appPath;

class ErrorHandlerCest
{
    public function logErrorOnError(UnitTester $I)
    {
        $diContainer = new FactoryDefault();
        $provider = new ConfigProvider();
        $provider->register($diContainer);
        $provider = new LoggerProvider();
        $provider->register($diContainer);

        /** @var Config $config */
        $config = $diContainer->getShared('config');
        /** @var Logger $logger */
        $logger = $diContainer->getShared('log');

        $logger->error('baka');
        $fileName = appPath('storage/logs/api.log');
        $I->openFile($fileName);
        $expected = '[ERROR] baka';
        $I->seeInThisFile($expected);
    }

    public function logErrorOnShutdown(UnitTester $I)
    {
        $diContainer = new FactoryDefault();
        $provider = new ConfigProvider();
        $provider->register($diContainer);
        $provider = new LoggerProvider();
        $provider->register($diContainer);

        /** @var Config $config */
        $config = $diContainer->getShared('config');
        /** @var Logger $logger */
        $logger = $diContainer->getShared('log');
        $handler = new ErrorHandler($logger, $config);

        $handler->shutdown();
        $fileName = appPath('storage/logs/api.log');
        $I->openFile($fileName);
        $expected = '[INFO] Shutdown completed';
        $I->seeInThisFile($expected);
    }
}
