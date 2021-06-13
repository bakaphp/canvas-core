<?php

declare(strict_types=1);

namespace Canvas\Contracts;

use Baka\Contracts\Auth\UserInterface;
use Canvas\Auth\Factory;
use Canvas\Auth\TokenResponse;
use Canvas\Models\Sessions;
use Canvas\Models\Users;

trait AuthTrait
{
    protected $userLinkedSourcesModel;
    protected $userModel;

    /**
     * Get the current user Ip.
     */
    protected function getClientIp() : string
    {
        return  is_string($this->request->getClientAddress(true)) ? $this->request->getClientAddress(true) : '127.0.0.1';
    }

    /**
     * Login user.
     *
     * @param string
     *
     * @return array
     */
    protected function loginUsers(string $email, string $password) : array
    {
        $userIp = $this->getClientIp();

        $remember = 1;
        $admin = 0;
        $auth = Factory::create($this->app->ecosystemAuth());
        $userData = $auth::login($email, $password, $remember, $admin, $userIp);

        return $this->authResponse($userData);
    }

    /**
     * User Login Social.
     *
     * @param string
     *
     * @return array
     */
    protected function loginSocial(Users $user) : array
    {
        $userIp = $this->getClientIp();

        $user->lastvisit = date('Y-m-d H:i:s');
        $user->user_login_tries = 0;
        $user->user_last_login_try = 0;
        $user->update();

        return $this->authResponse($user);
    }

    /**
     * Get the user Auth Response.
     *
     * @param UserInterface $user
     *
     * @return array
     */
    protected function authResponse(UserInterface $user) : array
    {
        $userIp = $this->getClientIp();
        $pageId = 1;
        $tokenResponse = TokenResponse::create($user);

        //start session
        $session = new Sessions();
        $session->start($user, $tokenResponse['sessionId'], $tokenResponse['token'], $userIp, $pageId);

        return $tokenResponse;
    }

    /**
     * Set user into Di by id.
     *
     * @param int $usersId
     *
     * @return void
     */
    protected function overWriteUserDataProvider(int $usersId) : void
    {
        $hostUser = Users::findFirstOrFail([
            'conditions' => 'id = ?0 and status = 1 and is_deleted = 0',
            'bind' => [$usersId]
        ]);

        /**
         * Set the host in di.
         */
        if (!$this->di->has('userData')) {
            $this->di->setShared('userData', $hostUser);
        }
    }
}
