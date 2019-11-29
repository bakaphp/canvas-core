<?php

declare(strict_types=1);

namespace Canvas\Middleware;

use Canvas\Http\Request;
use Canvas\Traits\TokenTrait;
use Canvas\Traits\SubscriptionPlanLimitTrait;
use Phalcon\Mvc\Micro\MiddlewareInterface;
use Canvas\Exception\SubscriptionPlanFailureException;
use Phalcon\Mvc\Micro;
use Phalcon\Http\RequestInterface;
use Canvas\Models\Subscription;

/**
 * Class AuthenticationMiddleware.
 *
 * @package Niden\Middleware
 */
abstract class TokenBase implements MiddlewareInterface
{
    use TokenTrait;
    use SubscriptionPlanLimitTrait;

    /**
     * @param Request $request
     * @return bool
     */
    protected function isValidCheck(RequestInterface $request, Micro $app): bool
    {
        /**
         * update this logic to a specific middleware or?
         */
        /*
        $calledRoute = $app['router']->getMatchedRoute()->getCompiledPattern();

        $user = $app->getDI()->getUserData();
        $isSubscriptionActive = Subscription::getByDefaultCompany($user)->active();
        if (isset($app['userData']) && !$isSubscriptionActive) {
            if (!isset($this->bypassRoutes[$calledRoute])) {
                throw new SubscriptionPlanFailureException('Subscription expired, update payment method or verify payment');
            } else {
                if (!in_array($request->getMethod(), $this->bypassRoutes[$calledRoute])) {
                    throw new SubscriptionPlanFailureException('Subscription expired, update payment method or verify payment');
                }
            }
        } */

        return true;
    }
}
