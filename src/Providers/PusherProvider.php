<?php

namespace Canvas\Providers;

use Phalcon\Di\ServiceProviderInterface;
use Phalcon\Di\DiInterface;
use Pusher\Pusher;

class PusherProvider implements ServiceProviderInterface
{
    /**
     * @param DiInterface $container
     */
    public function register(DiInterface $container) : void
    {
        $config = $container->getShared('config');

        $container->setShared(
            'pusher',
            function () use ($config) {
                return new Pusher(
                    $config->pusher->key,
                    $config->pusher->secret,
                    $config->pusher->id,
                    [
                        'cluster' => $config->pusher->cluster,
                        'useTLS' => true,
                        'debug' => true,
                    ]
                );
            }
        );
    }
}
