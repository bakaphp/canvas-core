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
}
