<?php

declare(strict_types=1);

namespace Canvas\Middleware;

use Canvas\Http\Request;
use Canvas\Traits\TokenTrait;
use Canvas\Traits\SubscriptionPlanLimitTrait;
use Phalcon\Mvc\Micro\MiddlewareInterface;
use Canvas\Exception\UnauthorizedHttpException;
use Phalcon\Mvc\Micro;
use Phalcon\Http\RequestInterface;
use Canvas\Models\Subscription;
use Baka\Http\RouterCollection;
use Phalcon\Di;

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
     *
     * @return bool
     */
    protected function isValidCheck(RequestInterface $request, Micro $app): bool
    {
        $ignoreJwt = $request->ignoreJwt($app['router']->getMatchedRoute());

        $calledRoute = $app['router']->getMatchedRoute()->getCompiledPattern();
        
        if(Di::getDefault()->has('userData') && !Subscription::getPaymentStatus()){
            if (!array_key_exists($calledRoute,$this->bypassRoutes) && !in_array($request->getMethod(),$this->bypassRoutes[$calledRoute])) {
                throw new UnauthorizedHttpException('Subscription expired,update payment method or verify payment');
            }
        }
        

        if (!$ignoreJwt && $request->isEmptyBearerToken()) {
            throw new UnauthorizedHttpException('Missing Token');
        }

        return !$ignoreJwt;
    }
}
