<?php

declare(strict_types=1);

namespace Canvas\Providers;

use Canvas\Http\Request;
use Canvas\Http\SwooleRequest;
use Phalcon\Di\ServiceProviderInterface;
use Phalcon\DiInterface;
use function Canvas\Core\isSwooleServer;
use OakLabs\PhalconThrottler\RedisThrottler;

class ThrottleProvider implements ServiceProviderInterface
{
    /**
     * @param DiInterface $container
     */
    public function register(DiInterface $container)
    {
        $config = $container->getShared('config');

        $container->setShared('throttler', function () use ($container)  {
            return new RedisThrottler($container->getShared('redis'), [
                'bucket_size'  => 2,
                'refill_time'  => 120, // 10m
                'refill_amount'  => 1
            ]);
        });
    }
}
