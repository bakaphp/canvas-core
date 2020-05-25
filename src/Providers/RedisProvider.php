<?php

namespace Canvas\Providers;

use function Canvas\Core\envValue;
use Phalcon\Di\ServiceProviderInterface;
use Phalcon\DiInterface;
use Redis;

class RedisProvider implements ServiceProviderInterface
{
    /**
     * @param DiInterface $container
     */
    public function register(DiInterface $container)
    {
        $app = envValue('GEWAER_APP_ID', 1);

        $container->setShared(
            'redis',
            function () use ($app) {
                //Connect to redis
                $redis = new Redis();
                $redis->connect(envValue('REDIS_HOST', '127.0.0.1'), (int) envValue('REDIS_PORT', 6379));
                $redis->setOption(Redis::OPT_PREFIX, $app . ':');	// use custom prefix on all keys
                $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
                return $redis;
            }
        );
    }
}
