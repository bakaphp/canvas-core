<?php

declare(strict_types=1);

namespace Canvas\Middleware;

use Baka\Auth\UserProvider;
use Baka\Http\Exception\UnauthorizedException;
use Canvas\Constants\Flags;
use Canvas\Models\Apps;
use Canvas\Models\AppsKeys;
use Canvas\Models\Sessions;
use Canvas\Models\Users;
use Lcobucci\JWT\Token;
use Phalcon\Config;
use Phalcon\Http\RequestInterface;
use Phalcon\Mvc\Micro;

/**
 * Class AuthenticationMiddleware.
 *
 * @package Canvas\Middleware
 */
class AuthenticationMiddleware extends TokenBase
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
        $config = $api->getService('config');
        $request = $api->getService('request');

        /**
         * This is where we will find if the user exists based on
         * the token passed using Bearer Authentication.
         */
        if (!empty($request->getBearerTokenFromHeader())) {
            $token = $this->getToken($request->getBearerTokenFromHeader());
        } elseif ($request->hasHeader('Client-Id') && $request->hasHeader('Client-Secret-Id') && $request->hasHeader('KanvasKey')) {
            // Functions that authenticates user by client id, client secret id and app key
            $this->sessionSdk($api, $request);

            return true;
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
     * @param Token $token
     * @param RequestInterface $request
     *
     * @throws UnauthorizedException
     *
     * @return void
     */
    protected function sessionUser(Micro $api, Config $config, Token $token, RequestInterface $request) : void
    {
        $api->getDI()->setShared(
            'userData',
            function () use ($config, $token, $request) {
                $session = new Sessions();
                $userData = UserProvider::get();

                //all is empty and is dev, ok take use the first user
                if (empty($token->claims()->get('sessionId')) && strtolower($config->app->env) == Flags::DEVELOPMENT) {
                    return $userData->findFirst(1);
                }

                if (!empty($token->claims()->get('sessionId'))) {
                    //user
                    if (!$user = $userData->getByEmail($token->claims()->get('email'))) {
                        throw new UnauthorizedException('User not found');
                    }

                    $ip = !defined('API_TESTS') ? $request->getClientAddress(true) : '127.0.0.1';
                    return $session->check($user, $token->claims()->get('sessionId'), (string) $ip, 1);
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
        if (!Users::validateJwtToken($token)) {
            throw new UnauthorizedException('Invalid Token');
        }
    }

    /**
     * Anonymous user from token.
     *
     * @param Micro $api
     * @param Config $config
     * @param mixed $token
     * @param RequestInterface $request
     *
     * @return void
     */
    protected function anonymousUser(Micro $api, Config $config, $token, RequestInterface $request) : void
    {
        $api->getDI()->setShared(
            'userData',
            function () use ($config) {
                /**
                 * @todo we need to track session for anonymous user
                 */
                if ($anonymous = Users::findFirst('-1')) {
                    return $anonymous;
                }

                throw new UnauthorizedException(
                    strtolower($config->app->env) == Flags::DEVELOPMENT ?
                    'No anonymous user configured in the app' :
                    'Missing Token'
                );
            }
        );
    }

    /**
     * Authenticate Admin user by client id, client secret id and apps keys.
     *
     * @param Micro $api
     * @param RequestInterface $request
     *
     * @return void
     *
     * @todo Add users validation by client id, client secret id, apps_id
     */
    protected function sessionSdk(Micro $api, RequestInterface $request) : void
    {
        $app = $api->getService('app');

        $api->getDI()->setShared(
            'userData',
            function () use ($request, $app) {
                $appkeys = AppsKeys::validateAppsKeys(
                    $request->getHeader('Client-Id'),
                    $request->getHeader('Client-Secret-Id'),
                    $app->getId()
                );

                return Users::findFirst($appkeys->users_id);
            }
        );
    }
}
