<?php

declare(strict_types=1);

namespace Canvas\Providers;

use function Baka\isSwooleServer;
use Canvas\Http\Request;
use Canvas\Http\SwooleRequest;
use Phalcon\Di\DiInterface;
use Phalcon\Di\ServiceProviderInterface;

class RequestProvider implements ServiceProviderInterface
{
    /**
     * @param DiInterface $container
     */
    public function register(DiInterface $container) : void
    {
        if (isSwooleServer()) {
            $container->setShared('request', new SwooleRequest());
        } else {
            $container->setShared('request', new Request());
        }
    }
}
