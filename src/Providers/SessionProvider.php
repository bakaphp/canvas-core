<?php

namespace Canvas\Providers;

use function Canvas\Core\envValue;
use Phalcon\Di\ServiceProviderInterface;
use Phalcon\DiInterface;
use Phalcon\Session\Adapter\Redis;

class SessionProvider implements ServiceProviderInterface
{
    /**
     * @param DiInterface $container
     */
    public function register(DiInterface $container)
    {
        $app = envValue('GEWAER_APP_ID', 1);

        $container->setShared(
            'session',
            function () use ($app) {
                $session = new Redis(
                    [
                        'uniqueId' => $app,
                        'host' => envValue('REDIS_HOST', '127.0.0.1'),
                        'port' => (int) envValue('REDIS_PORT', 6379),
                        'persistent' => false,
                        'lifetime' => 3600,
                        'prefix' => 'session',
                    ]
                );

                $session->start();

                return $session;
            }
        );
    }
}
