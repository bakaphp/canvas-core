<?php

declare(strict_types=1);

namespace Canvas\Providers;

use Phalcon\Cli\Dispatcher;
use Phalcon\Di\DiInterface;
use Phalcon\Di\ServiceProviderInterface;

class CliDispatcherProvider implements ServiceProviderInterface
{
    /**
     * @param DiInterface $container
     */
    public function register(DiInterface $container) : void
    {
        $container->setShared(
            'dispatcher',
            function () use ($container) {
                $config = $container->getShared('config');

                $dispatcher = new Dispatcher();
                $dispatcher->setDefaultNamespace(ucfirst($config->app->namespaceName) . '\Cli\Tasks');

                return $dispatcher;
            }
        );
    }
}
