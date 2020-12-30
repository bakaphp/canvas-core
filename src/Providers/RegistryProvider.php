<?php

namespace Canvas\Providers;

use Phalcon\Di\ServiceProviderInterface;
use Phalcon\Di\DiInterface;
use Phalcon\Registry;

class RegistryProvider implements ServiceProviderInterface
{
    /**
     * @param DiInterface $container
     */
    public function register(DiInterface $container) : void
    {
        $container->setShared(
            'registry',
            function () {
                return new Registry();
            }
        );
    }
}
