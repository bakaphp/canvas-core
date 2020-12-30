<?php

namespace Canvas\Providers;

use function Baka\envValue;
use Phalcon\Di\DiInterface;
use Phalcon\Di\ServiceProviderInterface;
use Redis;

class RedisProvider implements ServiceProviderInterface
{
    /**
     * @param DiInterface $container
     */
    public function register(DiInterface $container) : void
    {
        $app = envValue('GEWAER_APP_ID', 1);

        $container->setShared(
            'redis',
            function (bool $prefix = true) use ($app) {
                $redis = new Redis();
                $redis->connect(envValue('REDIS_HOST', '127.0.0.1'), (int) envValue('REDIS_PORT', 6379));
                if ($prefix) {
                    $redis->setOption(Redis::OPT_PREFIX, $app . ':'); // use custom prefix on all keys
                }

                //igbinary serialization is faster than PHP internal
                $serializeEngine = !extension_loaded('igbinary') ? Redis::SERIALIZER_PHP : Redis::SERIALIZER_IGBINARY;
                $redis->setOption(Redis::OPT_SERIALIZER, $serializeEngine);
                return $redis;
            }
        );

        /**
         * Redis with no serialize
         * need for sort function.
         */
        $container->setShared(
            'redisUnSerialize',
            function (bool $prefix = true) use ($app) {
                $redis = new Redis();
                $redis->connect(envValue('REDIS_HOST', '127.0.0.1'), (int) envValue('REDIS_PORT', 6379));
                if ($prefix) {
                    $redis->setOption(Redis::OPT_PREFIX, $app . ':'); // use custom prefix on all keys
                }
                return $redis;
            }
        );
    }
}
