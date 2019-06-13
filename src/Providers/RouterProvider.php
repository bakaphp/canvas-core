<?php

declare(strict_types=1);

namespace Canvas\Providers;

use function Canvas\Core\appPath;
use Baka\Router\Providers\RouterProvider as BakaRouterProvider;

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
        $path = appPath('api/routes');

        $routes = [
            'api' => $path . '/api.php',
        ];

        return $routes;
    }
}
