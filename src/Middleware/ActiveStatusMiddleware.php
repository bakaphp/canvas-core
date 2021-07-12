<?php

declare(strict_types=1);

namespace Canvas\Middleware;

use Phalcon\Mvc\Micro;
use Baka\Http\Exception\UnauthorizedException;
use Canvas\Models\Subscription;
use Canvas\Models\UsersAssociatedApps;


class ActiveStatusMiddleware extends TokenBase
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

            $userAssociatedApp = UsersAssociatedApps::getByUserId($user->getId());

            if (!$userAssociatedApp->validateIsActive()) {
                throw new UnauthorizedException("Current user is not active for this app's company");
            }
        }

        return true;
    }
}
