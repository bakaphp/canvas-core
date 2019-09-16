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
                $prefix = 'data';
                $frontAdapter = Data::class;
                $frontOptions = [
                    'lifetime' => envValue('CACHE_LIFETIME', 86400),
                ];
                $backOptions = [
                    'servers' => [
                        0 => [
                            'host' => envValue('REDIS_HOST', '127.0.0.1'),
                            'port' => envValue('REDIS_PORT', 6379)
                        ],
                    ],
                    'client' => [
                        Redis::OPT_PREFIX_KEY => 'api-',
                    ],
                    'lifetime' => 3600,
                    'prefix' => $prefix . '-',
                ];

                return new Libmemcached(new $frontAdapter($frontOptions), $backOptions);
            }
        );
    }
}
