<?php

namespace Canvas\Providers;

use Phalcon\Di\ServiceProviderInterface;
use Phalcon\DiInterface;
use Canvas\Models\Apps;
use Canvas\Exception\ServerErrorHttpException;

class AppProvider implements ServiceProviderInterface
{
    /**
     * @param DiInterface $container
     */
    public function register(DiInterface $container)
    {
        $config = $container->getShared('config');

        $container->setShared(
            'app',
            function () use ($config) {
                $app = Apps::findFirstByKey($config->app->id);
                if (!$app) {
                    throw new ServerErrorHttpException('No App configure with this key ' . $config->app->id);
                }
                return $app;
            }
        );
    }
}
