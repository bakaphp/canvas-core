<?php

namespace Canvas\Tests\unit\library\Providers;

use Canvas\Providers\CacheDataProvider;
use Canvas\Providers\ConfigProvider;
use Phalcon\Cache;
use Phalcon\Cache\Backend\Libmemcached;
use Phalcon\Di\FactoryDefault;
use UnitTester;

class CacheCest
{
    /**
     * @param UnitTester $I
     */
    public function checkRegistration(UnitTester $I)
    {
        $diContainer = new FactoryDefault();
        $config = new ConfigProvider();
        $config->register($diContainer);
        $provider = new CacheDataProvider();
        $provider->register($diContainer);

        $I->assertTrue($diContainer->has('cache'));
        /** @var Libmemcached $cache */
        $cache = $diContainer->getShared('cache');
        $I->assertTrue($cache instanceof Cache);
    }
}
