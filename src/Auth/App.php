<?php

declare(strict_types=1);

namespace Canvas\Auth;

use Canvas\Models\Users;
use Exception;
use Phalcon\Di;

class App extends Auth
{
    /**
     * User login.
     *
     * @param string $email
     * @param string $password
     * @param integer $autologin
     * @param integer $admin
     * @param string $userIp
     * @return Users
     */
    public static function login(string $email, string $password, int $autologin = 1, int $admin, string $userIp) : Users
    {
        //trim email
        $email = ltrim(trim($email));
        $password = ltrim(trim($password));

        //if its a email lets by it by email, if not by displayname
        $user = Users::getByEmail($email);

        //first we find the user
        if (!$user) {
            throw new Exception(_('Invalid Username or Password.'));
        }

        self::loginAttempsValidation($user);

        //check if the user exist on this app
        $currentAppUserInfo = $user->getApps([
            'conditions' => 'companies_id = ?0 AND apps_id = ?1',
            'bind' => [$user->currentCompanyId(), Di::getDefault()->getApp()->getId()]
        ]);

        if (!is_object($currentAppUserInfo)) {
            throw new RuntimeException('User not found for this current app');
        }

        //password verification
        if (password_verify($password, trim($currentAppUserInfo->password)) && $user->isActive()) {
            //rehash password
            self::passwordNeedRehash($password, $currentAppUserInfo);

            // Reset login tries
            self::resetLoginTries($user);
            return $user;
        } elseif ($user->isActive) {
            // Only store a failed login attempt for an active user - inactive users can't login even with a correct password
            self::updateLoginTries($user);

            throw new Exception(_('Invalid Username or Password.'));
        } elseif ($user->isBanned()) {
            throw new Exception(_('User has not been banned, please check your email for the activation link.'));
        } else {
            throw new Exception(_('User has not been activated, please check your email for the activation link.'));
        }
    }
}
