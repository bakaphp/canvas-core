<?php

namespace Canvas\Tests\unit\library\Providers;

use Canvas\Providers\RedisProvider;
use Canvas\Providers\ConfigProvider;
use Canvas\Providers\DatabaseProvider;
use Phalcon\Di\FactoryDefault;
use UnitTester;
use Redis;

class RedisCest
{
    /**
     * @param UnitTester $I
     */
    public function checkRegistration(UnitTester $I)
    {
        $diContainer = new FactoryDefault();
        $provider = new ConfigProvider();
        $provider->register($diContainer);
        $provider = new DatabaseProvider();
        $provider->register($diContainer);
        $provider = new RedisProvider();
        $provider->register($diContainer);

        $I->assertTrue($diContainer->has('redis'));

        $redis = $diContainer->getShared('redis');
        $I->assertTrue($redis instanceof Redis);
    }
}
