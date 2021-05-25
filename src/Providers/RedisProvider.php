<?php

namespace Canvas\Providers;

use function Baka\envValue;
use Phalcon\Di\DiInterface;
use Phalcon\Di\ServiceProviderInterface;
use Redis;
use RedisCluster;

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
                if (!envValue('REDIS_CLUSTER', 0)) {
                    $redis = new Redis();
                    $redis->connect(
                        envValue('REDIS_HOST', '127.0.0.1'),
                        (int) envValue('REDIS_PORT', 6379)
                    );
                } else {
                    $clusters = explode(',', envValue('REDIS_HOST', '127.0.0.1'));
                    $clusters = array_map('trim', $clusters);

                    $redis = new RedisCluster(null, $clusters, 1.5, 1.5);
                }
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
        $container->setShared(
            'redisUnSerialize',
            function (bool $prefix = true) use ($app) {
                if (!envValue('REDIS_CLUSTER', 0)) {
                    $redis = new Redis();
                    $redis->connect(
                        envValue('REDIS_HOST', '127.0.0.1'),
                        (int) envValue('REDIS_PORT', 6379)
                    );
                } else {
                    $clusters = explode(',', envValue('REDIS_HOST', '127.0.0.1'));
                    $clusters = array_map('trim', $clusters);

                    $redis = new RedisCluster(null, $clusters, 1.5, 1.5);
                }

                if ($prefix) {
                    $redis->setOption(Redis::OPT_PREFIX, $app . ':'); // use custom prefix on all keys
                }
                return $redis;
            }
        );
    }
}
