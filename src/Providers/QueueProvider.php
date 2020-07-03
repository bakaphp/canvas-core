<?php

namespace Canvas\Providers;

use Phalcon\Di\ServiceProviderInterface;
use Phalcon\Di\DiInterface;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use function Baka\envValue;

class QueueProvider implements ServiceProviderInterface
{
    /**
     * @param DiInterface $container
     */
    public function register(DiInterface $container) : void
    {
        $container->setShared(
            'queue',
            function () {
                //Connect to the queue
                $queue = new AMQPStreamConnection(
                    envValue('RABBITMQ_HOST', 'localhost'),
                    envValue('RABBITMQ_PORT', 5672),
                    envValue('RABBITMQ_DEFAULT_USER', 'guest'),
                    envValue('RABBITMQ_DEFAULT_PASS', 'guest'),
                    envValue('RABBITMQ_DEFAULT_VHOST', '/')
                );

                return $queue;
            }
        );
    }
}
