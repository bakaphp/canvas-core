<?php

declare(strict_types=1);

namespace Canvas\Providers;

use function Canvas\Core\appPath;
use Canvas\Middleware\NotFoundMiddleware;
use Canvas\Middleware\AuthenticationMiddleware;
use Canvas\Middleware\TokenValidationMiddleware;
use Canvas\Middleware\AclMiddleware;
use Baka\Router\Providers\RouterProvider as BakaRouterProvider;
use Phalcon\DiInterface;
use Phalcon\Events\Manager;
use Phalcon\Mvc\Micro;

class RouterProvider extends BakaRouterProvider
{
    /**
     * @inheritDoc
     *
     * @return array
     */
    protected function getCollections(): array
    {
        $routerCollections = [];

        foreach ($this->getRoutes() as $routePath) {
            array_push($routerCollections, ...require($routePath));
        }

        return $routerCollections;
    }

    /**
     * Returns the array for all the routes on this system.
     *
     * @return array
     */
    protected function getRoutes(): array
    {
        $path = appPath('/routes');

        $routes = [
            'api' => $path . '/api.php',
        ];

        if (!defined('API_TESTS')) {
            $path = appPath('api/routes');

            $routes = [
                'api' => $path . '/api.php',
            ];
        }

        return $routes;
    }
}
