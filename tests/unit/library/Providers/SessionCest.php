<?php

namespace Canvas\Tests\unit\library\Providers;

use Canvas\Providers\ConfigProvider;
use Canvas\Providers\SessionProvider;
use Phalcon\Di\FactoryDefault;
use Phalcon\Session\Manager;
use UnitTester;

class SessionCest
{
    /**
     * @param UnitTester $I
     */
    public function checkRegistration(UnitTester $I)
    {
        $diContainer = new FactoryDefault();
        $provider = new ConfigProvider();
        $provider->register($diContainer);

        $provider = new SessionProvider();
        $provider->register($diContainer);

        $I->assertTrue($diContainer->has('session'));

        $session = $diContainer->getShared('session');

        $I->assertTrue($session instanceof Manager);
    }
}
