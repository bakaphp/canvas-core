<?php

namespace Canvas\Providers;

use function Baka\envValue;
use Phalcon\Di\DiInterface;
use Phalcon\Di\ServiceProviderInterface;
use Phalcon\Session\Adapter\Redis;
use Phalcon\Session\Manager;
use Phalcon\Storage\AdapterFactory;
use Phalcon\Storage\SerializerFactory;

class SessionProvider implements ServiceProviderInterface
{
    /**
     * @param DiInterface $container
     */
    public function register(DiInterface $container) : void
    {
        $app = envValue('GEWAER_APP_ID', 1);

        $container->setShared(
            'session',
            function () use ($app) {
                $options = [
                    'uniqueId' => $app,
                    'host' => envValue('REDIS_HOST', '127.0.0.1'),
                    'port' => (int) envValue('REDIS_PORT', 6379),
                    'index' => '1',
                    'prefix' => 'session',
                ];

                $session = new Manager();
                $serializerFactory = new SerializerFactory();
                $factory = new AdapterFactory($serializerFactory);
                $redis = new Redis($factory, $options);

                $session
                    ->setAdapter($redis)
                    ->start();

                return $session;
            }
        );
    }
}
