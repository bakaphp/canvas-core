<?php

declare(strict_types=1);

namespace Canvas\Middleware;

use Canvas\Http\Request;
use Canvas\Traits\TokenTrait;
use Phalcon\Mvc\Micro\MiddlewareInterface;
use Canvas\Exception\UnauthorizedHttpException;
use Phalcon\Mvc\Micro;
use Phalcon\Http\RequestInterface;
/**
 * Class AuthenticationMiddleware.
 *
 * @package Niden\Middleware
 */
abstract class TokenBase implements MiddlewareInterface
{
    use TokenTrait;

    /**
     * @param Request $request
     *
     * @return bool
     */
    protected function isValidCheck(RequestInterface $request, Micro $app): bool
    {
        $ignoreJwt = $request->ignoreJwt($app['router']->getMatchedRoute());
        if (!$ignoreJwt && $request->isEmptyBearerToken()) {
            throw new UnauthorizedHttpException('Missing Token');
        }

        return !$ignoreJwt;
    }
}
