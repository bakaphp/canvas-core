<?php

declare(strict_types=1);

namespace Canvas\Middleware;

use Canvas\Contracts\Jwt\TokenTrait;
use Canvas\Contracts\SubscriptionPlanLimitTrait;
use Phalcon\Mvc\Micro\MiddlewareInterface;
use Canvas\Bootstrap\Micro;
use Illuminate\Http\Request;
use Closure;

abstract class TokenBase implements MiddlewareInterface
{
    use TokenTrait;
    use SubscriptionPlanLimitTrait;

    /**
     * Calls the middleware
     */
    public function handle(Request $request, Closure $next)
    {
    }
}
