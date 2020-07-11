<?php

namespace Canvas\Providers;

use function Baka\envValue;
use Phalcon\Cache;
use Phalcon\Cache\Adapter\Redis;
use Phalcon\Cache\AdapterFactory;
use Phalcon\Di\DiInterface;
use Phalcon\Di\ServiceProviderInterface;
use Phalcon\Storage\SerializerFactory;

class CacheDataProvider implements ServiceProviderInterface
{
    /**
     * @param DiInterface $container
     */
    public function register(DiInterface $container) : void
    {
        $config = $container->getShared('config');
        $app = envValue('GEWAER_APP_ID', 1);

        $container->setShared(
            'cache',
            function () use ($config, $app) {
                //Connect to redis
                $cache = $config->get('cache')->toArray();
                $adapter = $cache['adapter'];
                $options = $cache['options'][$adapter] ?? [];

                $options['prefix'] = $app . '-app-cache';

                $serializerFactory = new SerializerFactory();
                $adapterFactory = new AdapterFactory($serializerFactory);
                $adapter = $adapterFactory->newInstance($adapter, $options);

                return new Cache($adapter);
            }
        );
    }
}
