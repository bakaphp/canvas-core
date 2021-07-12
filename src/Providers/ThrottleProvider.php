<?php

declare(strict_types=1);

namespace Canvas\Providers;

use OakLabs\PhalconThrottler\RedisThrottler;
use Phalcon\Di\DiInterface;
use Phalcon\Di\ServiceProviderInterface;

class ThrottleProvider implements ServiceProviderInterface
{
    /**
     * @param DiInterface $container
     */
    public function register(DiInterface $container) : void
    {
        $container->setShared('throttler', function () use ($container) {
            $config = $container->getShared('config');

            return new RedisThrottler($container->getShared('redis'), [
                'bucket_size' => $config->throttle->bucketSize,
                'refill_time' => $config->throttle->refillTime,
                'refill_amount' => $config->throttle->refillAmount
            ]);
        });
    }
}
