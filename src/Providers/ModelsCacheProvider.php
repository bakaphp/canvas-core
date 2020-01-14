<?php

declare(strict_types=1);

namespace Canvas\Providers;

use function Canvas\Core\envValue;
use Phalcon\Cache\Backend\Memory;
use Phalcon\Cache\Backend\Redis;
use Phalcon\Cache\Frontend\Data;
use Phalcon\Cache\Frontend\None;
use Phalcon\DiInterface;
use Phalcon\Di\ServiceProviderInterface;

class ModelsCacheProvider implements ServiceProviderInterface
{
    /**
     * @param DiInterface $container
     */
    public function register(DiInterface $container)
    {
        $container->setShared(
            'modelsCache',
            function () use ($container) {
                if (!$container->getConfig()->app->production) {
                    $frontCache = new None();
                    $cache = new Memory($frontCache);
                } else {
                    $frontCache = new Data([
                        'lifetime' => envValue('MODELS_CACHE_LIFETIME', 86400),
                    ]);

                    $cache = new Redis(
                        $frontCache,
                        [
                            'host' => envValue('REDIS_HOST', '127.0.0.1'),
                            'port' => envValue('REDIS_PORT', 6379),
                            'prefix' => 'modelsCache',
                        ]
                    );
                }

                return $cache;
            }
        );
    }
}
