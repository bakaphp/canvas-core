<?php

declare(strict_types=1);

namespace Canvas\Providers;

use Baka\Constants\Flags;
use function Baka\envValue;
use Phalcon\Cache;
use Phalcon\Cache\AdapterFactory;
use Phalcon\Di\DiInterface;
use Phalcon\Di\ServiceProviderInterface;
use Phalcon\Storage\SerializerFactory;

class ModelsCacheProvider implements ServiceProviderInterface
{
    /**
     * @param DiInterface $container
     */
    public function register(DiInterface $container) : void
    {
        $config = $container->getShared('config');
        $app = envValue('GEWAER_APP_ID', 1);

        $container->setShared(
            'modelsCache',
            function () use ($config, $app) {
                $type = 'redis';
                if (strtolower($config->app->env) != Flags::PRODUCTION) {
                    $type = 'memory';
                }

                $serializerFactory = new SerializerFactory();
                $adapterFactory = new AdapterFactory($serializerFactory);

                $options = [
                    'defaultSerializer' => 'php',
                    'lifetime' => 7200
                ];

                $cache = $config->get('cache')->toArray();
                $options = $cache['metadata']['prod']['options'];
                $options['prefix'] = $app;

                $adapter = $adapterFactory->newInstance($type, $options);

                return new Cache($adapter);
            }
        );
    }
}
