<?php

declare(strict_types=1);

namespace Canvas\Middleware;

use Baka\Http\Exception\UnauthorizedException;
use Phalcon\Mvc\Micro;

class TokenExpirationMiddleware extends TokenBase
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
        $request = $api->getService('request');

        /**
         * This is where we will find if the user exists based on
         * the token passed using Bearer Authentication.
         */
        if (!empty($request->getBearerTokenFromHeader())) {
            $token = $this->getToken($request->getBearerTokenFromHeader());
        } else {
            throw new UnauthorizedException('Missing Token');
        }

        if ($token->isExpired()) {
            throw new UnauthorizedException('Expired Token');
        }

        return true;
    }
}
