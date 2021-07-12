<?php

namespace Canvas\Providers;

use Phalcon\Di\DiInterface;
use Phalcon\Di\ServiceProviderInterface;
use Pusher\Pusher;

class PusherProvider implements ServiceProviderInterface
{
    /**
     * @param DiInterface $container
     */
    public function register(DiInterface $container) : void
    {
        $container->setShared(
            'pusher',
            function () use ($container) {
                $config = $container->getShared('config');

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
