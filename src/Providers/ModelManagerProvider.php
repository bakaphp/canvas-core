<?php

declare(strict_types=1);

namespace Canvas\Providers;

use Baka\Database\Manager as BakaModelManager;
use Phalcon\Di\DiInterface;
use Phalcon\Di\ServiceProviderInterface;

class ModelManagerProvider implements ServiceProviderInterface
{
    /**
     * DI registration.
     *
     * @param DiInterface $container
     *
     * @return void
     */
    public function register(DiInterface $container) : void
    {
        $container->setShared(
            'modelsManager',
            function () {
                return new BakaModelManager();
            }
        );
    }
}
