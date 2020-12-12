<?php

declare(strict_types=1);

namespace Canvas\Middleware;

use Canvas\Contracts\Jwt\TokenTrait;
use Canvas\Contracts\SubscriptionPlanLimitTrait;
use Phalcon\Mvc\Micro\MiddlewareInterface;

abstract class TokenBase implements MiddlewareInterface
{
    use TokenTrait;
    use SubscriptionPlanLimitTrait;
}
