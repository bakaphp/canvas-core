<?php

namespace Canvas\Providers;

use Phalcon\Di\ServiceProviderInterface;
use Phalcon\DiInterface;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use function Canvas\Core\envValue;

class QueueProvider implements ServiceProviderInterface
{
    /**
     * @param DiInterface $container
     */
    public function register(DiInterface $container)
    {
        $container->setShared(
            'queue',
            function () {
                //Connect to the queue
                $queue = new AMQPStreamConnection(
                    envValue('RABBITMQ_HOST', 'localhost'),
                    envValue('RABBITMQ_PORT', 5672),
                    envValue('RABBITMQ_DEFAULT_USER', 'guest'),
                    envValue('RABBITMQ_DEFAULT_PASS', 'guest')
                );

                return $queue;
            }
        );
    }
}
