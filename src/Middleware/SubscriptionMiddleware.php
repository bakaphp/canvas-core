<?php

declare(strict_types=1);

namespace Canvas\Middleware;

use Phalcon\Mvc\Micro;
use Canvas\Http\Exception\UnauthorizedException;
use Canvas\Models\Subscription;

/**
 * Class AuthenticationMiddleware.
 *
 * @package Niden\Middleware
 */
class SubscriptionMiddleware extends TokenBase
{
    /**
     * Call me.
     *
     * @param Micro $api
     * @todo need to check section for auth here
     * @return bool
     */
    public function call(Micro $api)
    {
        if ($api->getDI()->has('userData')) {
            $user = $api->getDI()->getUserData();

            if (!Subscription::getByDefaultCompany($user)->active()) {
                throw new UnauthorizedException('Subscription expired, Verify Payment');
            }
        }

        return true;
    }
}
