<?php

namespace Canvas\Providers;

use function Canvas\Core\envValue;
use Phalcon\Cache\Backend\Libmemcached;
use Phalcon\Cache\Frontend\Data;
use Phalcon\Di\ServiceProviderInterface;
use Phalcon\DiInterface;
use Redis;

class CacheDataProvider implements ServiceProviderInterface
{
    /**
     * @param DiInterface $container
     */
    public function register(DiInterface $container)
    {
        $container->setShared(
            'cache',
            function () {
                //Connect to redis
                $redis = new Redis();
                $redis->connect(envValue('REDIS_HOST', '127.0.0.1'), (int) envValue('REDIS_PORT', 6379));
                $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
                return $redis;
            }
        );
    }
}
