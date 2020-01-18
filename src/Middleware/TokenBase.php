<?php

declare(strict_types=1);

namespace Canvas\Middleware;

use Canvas\Traits\TokenTrait;
use Canvas\Traits\SubscriptionPlanLimitTrait;
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
