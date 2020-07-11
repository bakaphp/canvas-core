<?php

declare(strict_types=1);

namespace Canvas\Providers;

use Phalcon\Cli\Dispatcher;
use Phalcon\Di\ServiceProviderInterface;
use Phalcon\Di\DiInterface;

class CliDispatcherProvider implements ServiceProviderInterface
{
    /**
     * @param DiInterface $container
     */
    public function register(DiInterface $container) : void
    {
        $config = $container->getShared('config');

        $container->setShared(
            'dispatcher',
            function () use ($config) {
                $dispatcher = new Dispatcher();
                $dispatcher->setDefaultNamespace(ucfirst($config->app->namespaceName) . '\Cli\Tasks');

                return $dispatcher;
            }
        );
    }
}
