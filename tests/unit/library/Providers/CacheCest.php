<?php

namespace Canvas\Tests\unit\library\Providers;

use Canvas\Providers\CacheDataProvider;
use Phalcon\Cache\Backend\Libmemcached;
use Phalcon\Di\FactoryDefault;
use UnitTester;
use Redis;

class CacheCest
{
    /**
     * @param UnitTester $I
     */
    public function checkRegistration(UnitTester $I)
    {
        $diContainer = new FactoryDefault();
        $provider = new CacheDataProvider();
        $provider->register($diContainer);

        $I->assertTrue($diContainer->has('cache'));
        /** @var Libmemcached $cache */
        $cache = $diContainer->getShared('cache');
        $I->assertTrue($cache instanceof Redis);
    }
}
