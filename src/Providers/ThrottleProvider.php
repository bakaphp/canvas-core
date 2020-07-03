<?php

declare(strict_types=1);

namespace Canvas\Providers;

use Canvas\Http\Request;
use Canvas\Http\SwooleRequest;
use Phalcon\Di\ServiceProviderInterface;
use Phalcon\Di\DiInterface;
use function Baka\isSwooleServer;
use OakLabs\PhalconThrottler\RedisThrottler;

class ThrottleProvider implements ServiceProviderInterface
{
    /**
     * @param DiInterface $container
     */
    public function register(DiInterface $container) : void
    {
        $config = $container->getShared('config');

        $container->setShared('throttler', function () use ($container,$config)  {
            return new RedisThrottler($container->getShared('redis'), [
                'bucket_size'  => $config->throttle->bucketSize,
                'refill_time'  => $config->throttle->refillTime,
                'refill_amount'  => $config->throttle->refillAmount
            ]);
        });
    }
}
