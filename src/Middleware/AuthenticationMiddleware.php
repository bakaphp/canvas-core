<?php

declare(strict_types=1);

namespace Canvas\Middleware;

use Phalcon\Mvc\Micro;
use Phalcon\Mvc\Micro\MiddlewareInterface;
use Baka\Auth\Models\Sessions;
use Canvas\Models\Users;
use Canvas\Exception\UnauthorizedHttpException;
use Canvas\Constants\Flags;

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

        if ($this->isValidCheck($request, $api)) {
            /**
             * This is where we will find if the user exists based on
             * the token passed using Bearer Authentication.
             */
            $data = $this->getToken($request->getBearerTokenFromHeader());

            $api->getDI()->setShared(
                'userData',
                function () use ($config, $data, $request) {
                    $session = new Sessions();

                    //all is empty and is dev, ok take use the first user
                    if (empty($data->getClaim('sessionId')) && strtolower($config->app->env) == Flags::DEVELOPMENT) {
                        return Users::findFirst(1);
                    }

                    if (!empty($data->getClaim('sessionId'))) {
                        //user
                        if (!$user = Users::getByEmail($data->getClaim('email'))) {
                            throw new UnauthorizedHttpException('User not found');
                        }

                        $ip = !defined('API_TESTS') ? $request->getClientAddress() : '127.0.0.1';
                        return $session->check($user, $data->getClaim('sessionId'), (string) $ip, 1);
                    } else {
                        throw new UnauthorizedHttpException('User not found');
                    }
                }
            );
        }

        return true;
    }
}
