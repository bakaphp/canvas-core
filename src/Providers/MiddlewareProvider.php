<?php

declare(strict_types=1);

namespace Canvas\Providers;

use Baka\Router\Providers\MiddlewareProvider as BakaMiddlewareProvider;
use Canvas\Middleware\AclMiddleware;
use Canvas\Middleware\ActiveStatusMiddleware;
use Canvas\Middleware\AnonymousMiddleware;
use Canvas\Middleware\AuthenticationMiddleware;
use Canvas\Middleware\NotFoundMiddleware;
use Canvas\Middleware\SubscriptionMiddleware;
use Phalcon\Events\Manager;
use Phalcon\Mvc\Micro;

class MiddlewareProvider extends BakaMiddlewareProvider
{
    protected $canvasGlobalMiddlewares = [
        // Before the handler has been executed
        NotFoundMiddleware::class => 'before',
    ];

    /**
     * This are the routes you have access to via the Baka routes components.
     *
     * This follows the order you specify on this array so
     *  - Token will run before Auth
     *
     * @var array
     */
    protected $canvasRouteMiddlewares = [
        'auth.jwt' => AuthenticationMiddleware::class,
        'auth.anonymous' => AnonymousMiddleware::class,
        'auth.acl' => AclMiddleware::class,
        'auth.subscription' => SubscriptionMiddleware::class,
        'auth.activeStatus' => ActiveStatusMiddleware::class
    ];

    /**
     * Attaches the middleware to the application.
     *
     * @param Micro   $application
     * @param Manager $eventsManager
     */
    protected function attachMiddleware(Micro $application, Manager $eventsManager)
    {
        /**
         * Merge canvas Middleware with the app middleware.
         */
        $this->globalMiddlewares = array_merge($this->globalMiddlewares, $this->canvasGlobalMiddlewares);
        $this->routeMiddlewares = array_merge($this->routeMiddlewares, $this->canvasRouteMiddlewares);

        parent::attachMiddleware($application, $eventsManager);
    }
}
