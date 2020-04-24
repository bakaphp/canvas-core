<?php

declare(strict_types=1);

namespace Canvas\Middleware;

use Phalcon\Mvc\Micro;

/**
 * Class AuthenticationMiddleware.
 *
 * @package Canvas\Middleware
 */
class AnonymousMiddleware extends AuthenticationMiddleware
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
        $config = $api->getService('config');
        $request = $api->getService('request');

        $anonymousUser = false;
        $token = null;

        /**
         * This is where we will find if the user exists based on
         * the token passed using Bearer Authentication.
         */
        if (!empty($request->getBearerTokenFromHeader())) {
            $token = $this->getToken($request->getBearerTokenFromHeader());
        } else {
            $anonymousUser = true;
        }

        if (!$anonymousUser) {
            $this->sessionUser($api, $config, $token, $request);
        } else {
            $this->anonymousUser($api, $config, $token, $request);
        }

        return true;
    }
}
