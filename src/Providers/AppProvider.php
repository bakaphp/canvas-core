<?php

namespace Canvas\Providers;

use Phalcon\Di\ServiceProviderInterface;
use Phalcon\Di\DiInterface;
use Canvas\Models\Apps;
use Baka\Http\Exception\InternalServerErrorException;
use Phalcon\Http\Request;

class AppProvider implements ServiceProviderInterface
{
    /**
     * @param DiInterface $container
     */
    public function register(DiInterface $container) : void
    {
        $config = $container->getShared('config');
        
        $container->setShared(
            'app',
            function () use ($config) {
                //$request = new Request();
                //$appKey = $request->hasHeader('KanvasKey') ? $request->getHeader('KanvasKey') : $config->app->id;
                $appKey = $config->app->id;
                $app = Apps::findFirstByKey($appKey);
                if (!$app) {
                    throw new InternalServerErrorException('No App configure with this key ' . $appKey);
                }
                return $app;
            }
        );
    }
}
