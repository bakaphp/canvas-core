<?php

declare(strict_types=1);

namespace Canvas\Middleware;

use Canvas\Contracts\TokenTrait;
use Canvas\Contracts\SubscriptionPlanLimitTrait;
use Phalcon\Mvc\Micro\MiddlewareInterface;

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
