<?php

declare(strict_types=1);

namespace Canvas\Providers;

use function Baka\appPath;
use Baka\Router\Providers\RouterProvider as BakaRouterProvider;

class RouterProvider extends BakaRouterProvider
{
    /**
     * {@inheritdoc}
     *
     * @return array
     */
    protected function getCollections() : array
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
    protected function getRoutes() : array
    {
        //when testing change the path of routes
        $path = !defined('API_TESTS') ? appPath('api/routes') : appPath('routes');

        $routes = [
            'api' => $path . '/api.php',
        ];

        return $routes;
    }
}
