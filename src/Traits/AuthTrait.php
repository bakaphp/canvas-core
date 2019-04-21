<?php

declare(strict_types=1);

namespace Canvas\Traits;

use Canvas\Models\Users;
use Baka\Auth\Models\Sessions;

/**
 * Trait ResponseTrait
 *
 * @package Canvas\Traits
 *
 * @property Users $user
 * @property Config $config
 * @property Request $request
 * @property Auth $auth
 * @property \Phalcon\Di $di
 *
 */
trait AuthTrait
{
    /**
     * Login user
     * @param string
     * @return array
     */
    private function loginUsers(string $email, string $password): array
    {
        $userIp = !defined('API_TESTS') ? $this->request->getClientAddress() : '127.0.0.1';

        $userData = Users::login($email, $password, 1, 0, $userIp);
        $token = $userData->getToken();


        //start session
        $session = new Sessions();
        $session->start($userData, $token['sessionId'], $token['token'], $userIp, 1);

        return [
            'token' => $token['token'],
            'time' => date('Y-m-d H:i:s'),
            'expires' => date('Y-m-d H:i:s', time() + $this->config->jwt->payload->exp),
            'id' => $userData->getId(),
        ];
    }
}
