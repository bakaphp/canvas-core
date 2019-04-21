<?php

declare(strict_types=1);

namespace Canvas\Providers;

use function Canvas\Core\appPath;
use Canvas\Middleware\NotFoundMiddleware;
use Canvas\Middleware\AuthenticationMiddleware;
use Canvas\Middleware\TokenValidationMiddleware;
use Canvas\Middleware\AclMiddleware;
use Phalcon\Di\ServiceProviderInterface;
use Phalcon\DiInterface;
use Phalcon\Events\Manager;
use Phalcon\Mvc\Micro;

class RouterProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     *
     * @param DiInterface $container
     */
    public function register(DiInterface $container)
    {
        /** @var Micro $application */
        $application = $container->getShared('application');
        /** @var Manager $eventsManager */
        $eventsManager = $container->getShared('eventsManager');

        $this->attachRoutes($application);
        $this->attachMiddleware($application, $eventsManager);

        $application->setEventsManager($eventsManager);
    }

    /**
     * Attaches the middleware to the application.
     *
     * @param Micro   $application
     * @param Manager $eventsManager
     */
    protected function attachMiddleware(Micro $application, Manager $eventsManager)
    {
        $middleware = $this->getMiddleware();

        /**
         * Get the events manager and attach the middleware to it.
         */
        foreach ($middleware as $class => $function) {
            $eventsManager->attach('micro', new $class());
            $application->{$function}(new $class());
        }
    }

    /**
     * Attaches the routes to the application; lazy loaded.
     *
     * @param Micro $application
     */
    protected function attachRoutes(Micro $application)
    {
        $routes = $this->getRoutes();

        foreach ($routes as $route) {
            include $route;
        }
    }

    /**
     * Returns the array for the middleware with the action to attach.
     *
     * @return array
     */
    protected function getMiddleware(): array
    {
        return [
            TokenValidationMiddleware::class => 'before',
            NotFoundMiddleware::class => 'before',
            AuthenticationMiddleware::class => 'before',
            AclMiddleware::class => 'before',
        ];
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
