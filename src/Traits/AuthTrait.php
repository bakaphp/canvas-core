<?php

declare(strict_types=1);

namespace Canvas\Traits;

use Canvas\Models\Users;
use Baka\Auth\Models\Sessions;

/**
 * Trait ResponseTrait.
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
     * Login user.
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

    /**
     * User Login Social.
     * @param string
     * @return array
     */
    private function loginSocial(Users $user): array
    {
        $userIp = !defined('API_TESTS') ? $this->request->getClientAddress() : '127.0.0.1';

        $user->lastvisit = date('Y-m-d H:i:s');
        $user->user_login_tries = 0;
        $user->user_last_login_try = 0;
        $user->update();

        $token = $user->getToken();

        //start session
        $session = new Sessions();
        $session->start($user, $token['sessionId'], $token['token'], $userIp, 1);

        return [
            'token' => $token['token'],
            'time' => date('Y-m-d H:i:s'),
            'expires' => date('Y-m-d H:i:s', time() + $this->config->jwt->payload->exp),
            'id' => $user->getId(),
        ];
    }

    /**
     * Set user into Di by id.
     * @param int $usersId
     * @return void
     */
    private function setUserDataById(int $usersId): void
    {
        $hostUser = Users::findFirst([
            'conditions' => 'id = ?0 and status = 1 and is_deleted = 0',
            'bind' => [$usersId]
        ]);

        if (is_object($hostUser)) {
            throw new ModelException('Host User not found');
        }

        /**
         * Set the host in di.
         */
        if (!$this->di->has('userData')) {
            $this->di->setShared('userData', $hostUser);
        }
    }
}
