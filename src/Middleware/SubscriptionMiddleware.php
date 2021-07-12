<?php

declare(strict_types=1);

namespace Canvas\Middleware;

use Baka\Http\Exception\UnauthorizedException;
use Canvas\Models\Subscription;
use Phalcon\Mvc\Micro;

class SubscriptionMiddleware extends TokenBase
{
    /**
     * Call me.
     *
     * @param Micro $api
     *
     * @todo need to check section for auth here
     *
     * @return bool
     */
    public function call(Micro $api)
    {
        if ($api->getDI()->has('userData') && $api->getDI()->has('app')) {
            $user = $api->getDI()->getUserData();

            if (!Subscription::getByDefaultCompany($user)->active()) {
                throw new UnauthorizedException('Subscription expired, Verify Payment');
            }
        }

        return true;
    }
}
