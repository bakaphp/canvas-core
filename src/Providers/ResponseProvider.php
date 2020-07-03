<?php

declare(strict_types=1);

namespace Canvas\Providers;

use Canvas\Http\Response;
use Canvas\Http\SwooleResponse;
use Phalcon\Di\ServiceProviderInterface;
use Phalcon\Di\DiInterface;
use function Baka\isSwooleServer;

class ResponseProvider implements ServiceProviderInterface
{
    /**
     * @param DiInterface $container
     */
    public function register(DiInterface $container) : void
    {
        if (isSwooleServer()) {
            $container->setShared('response', new SwooleResponse());
        } else {
            $container->setShared('response', new Response());
        }
    }
}
