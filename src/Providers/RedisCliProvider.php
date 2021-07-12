<?php

namespace Canvas\Providers;

use function Baka\envValue;
use Baka\Redis\Pool;
use Phalcon\Di\DiInterface;
use Phalcon\Di\ServiceProviderInterface;
use Redis;

class RedisCliProvider implements ServiceProviderInterface
{
    /**
     * @param DiInterface $container
     */
    public function register(DiInterface $container) : void
    {
        $app = envValue('GEWAER_APP_ID', 1);

        $container->set(
            'redis',
            function (bool $prefix = true) use ($app) : Redis {
                $redisPool = new Pool();
                $redis = $redisPool->get();

                if ($prefix) {
                    $redis->setOption(Redis::OPT_PREFIX, $app . ':'); // use custom prefix on all keys
                }

                $serializeEngine = Redis::SERIALIZER_PHP;

                //igbinary serialization is faster than PHP internal
                if (extension_loaded('igbinary') && envValue('REDIS_SERIALIZER', 'igbinary') === 'igbinary') {
                    $serializeEngine = Redis::SERIALIZER_IGBINARY;
                }

                $redis->setOption(Redis::OPT_SERIALIZER, $serializeEngine);

                return $redis;
            }
        );

        /**
         * Redis with no serialize
         * need for sort function.
         */
        $container->set(
            'redisUnSerialize',
            function (bool $prefix = true) use ($app) : Redis {
                $redisPool = new Pool();
                $redis = $redisPool->get();
                $redis->connect(
                    envValue('REDIS_HOST', '127.0.0.1'),
                    (int) envValue('REDIS_PORT', 6379)
                );
                if ($prefix) {
                    $redis->setOption(Redis::OPT_PREFIX, $app . ':'); // use custom prefix on all keys
                }
                return $redis;
            }
        );
    }
}
