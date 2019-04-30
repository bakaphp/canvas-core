<?php

namespace Canvas\Providers;

use Phalcon\Di\ServiceProviderInterface;
use Phalcon\DiInterface;
use Phalcon\Queue\Beanstalk\Extended as Beanstalk;
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
                $queue = new \PhpAmqpLib\Connection\AMQPStreamConnection(
                    envValue('RABBITMQ_HOST'),
                    envValue('RABBITMQ_PORT'),
                    envValue('RABBITMQ_DEFAULT_USER'),
                    envValue('RABBITMQ_DEFAULT_PASS')
                );

                return $queue;
            }
        );

        // $container->setShared(
        //     'queue',
        //     function () {
        //         //Connect to the queue
        //         $queue = new Beanstalk([
        //             'host' => envValue('DATA_API_BEANSTALK_HOST', '127.0.0.1'),
        //             'prefix' => envValue('DATA_API_BEANSTALK_PORT', 11300),
        //         ]);
        //         return $queue;
        //     }
        // );
    }
}
