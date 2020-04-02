<?php

declare(strict_types=1);

namespace Canvas\Middleware;

use Phalcon\Mvc\Micro;
use Baka\Auth\Models\Sessions;
use Canvas\Models\Users;
use Canvas\Constants\Flags;
use Canvas\Http\Exception\UnauthorizedException;

/**
 * Class AuthenticationMiddleware.
 *
 * @package Niden\Middleware
 */
class AuthenticationMiddleware extends TokenBase
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

        /**
         * This is where we will find if the user exists based on
         * the token passed using Bearer Authentication.
         */
        if (!empty($request->getBearerTokenFromHeader())) {
            $token = $this->getToken($request->getBearerTokenFromHeader());
        } else {
            throw new UnauthorizedException('Missing Token');
        }

        $this->sessionUser($api, $config, $token, $request);

        return true;
    }

    /**
     * Get the real from the JWT Token.
     *
     * @param Micro $api
     * @param Config $config
     * @param string $token
     * @param RequestInterface $request
     * @throws UnauthorizedException
     * @return void
     */
    protected function sessionUser(Micro $api, Config $config, object $token, RequestInterface $request): void
    {
        $api->getDI()->setShared(
            'userData',
            function () use ($config, $token, $request) {
                $session = new Sessions();

                //all is empty and is dev, ok take use the first user
                if (empty($token->getClaim('sessionId')) && strtolower($config->app->env) == Flags::DEVELOPMENT) {
                    return Users::findFirst(1);
                }

                if (!empty($token->getClaim('sessionId'))) {
                    //user
                    if (!$user = Users::getByEmail($token->getClaim('email'))) {
                        throw new UnauthorizedException('User not found');
                    }

                    $ip = !defined('API_TESTS') ? $request->getClientAddress() : '127.0.0.1';
                    return $session->check($user, $token->getClaim('sessionId'), (string) $ip, 1);
                } else {
                    throw new UnauthorizedException('User not found');
                }
            }
        );

        /**
         * This is where we will validate the token that was sent to us
         * using Bearer Authentication.
         *
         * Find the user attached to this token
         */
        if (!$token->validate(Users::getValidationData($token->getHeader('jti')))) {
            throw new UnauthorizedException('Invalid Token');
        }
    }

    /**
     * Anonymous user from token.
     *
     * @param Micro $api
     * @param Config $config
     * @param string $token
     * @param RequestInterface $request
     * @return void
     */
    protected function anonymousUser(Micro $api, Config $config, $token, RequestInterface $request): void
    {
        $api->getDI()->setShared(
            'userData',
            function () use ($config, $token, $request) {
                /**
                 * @todo we need to track session for anonymous user
                 */
                if ($anonymous = Users::findFirst('-1')) {
                    return $anonymous;
                }

                throw new UnauthorizedException(
                    strtolower($config->app->env) == Flags::DEVELOPMENT ?
                    'No anonymous user configured in the app' :
                    'No user found guest'
                );
            }
        );
    }
}
