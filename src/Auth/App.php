<?php

declare(strict_types=1);

namespace Canvas\Auth;

use Baka\Auth\Auth;
use Baka\Hashing\Password;
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
     * @param int $autologin
     * @param int $admin
     * @param string $userIp
     *
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
            throw new Exception(_('Invalid email or password.'));
        }

        self::loginAttemptsValidation($user);

        //check if the user exist on this app
        $currentAppUserInfo = $user->getApp();

        if (!is_object($currentAppUserInfo) || empty($currentAppUserInfo->password)) {
            throw new Exception(_('Invalid email or password.'));
        }

        //password verification
        if (Password::check($password, $currentAppUserInfo->password) && $user->isActive()) {
            //rehash password
            Password::rehash($password, $currentAppUserInfo);

            // Reset login tries
            self::resetLoginTries($user);
            return $user;
        } elseif ($user->isActive()) {
            // Only store a failed login attempt for an active user - inactive users can't login even with a correct password
            self::updateLoginTries($user);

            throw new Exception(_('Invalid email or password.'));
        } elseif ($user->isBanned()) {
            throw new Exception(_('User has not been banned, please check your email for the activation link.'));
        } else {
            throw new Exception(_('User has not been activated, please check your email for the activation link.'));
        }
    }

    /**
     * Update the password for the current app of all the companies, FOR NOW.
     *
     * @param Users $user
     * @param string $password
     *
     * @return bool
     */
    public static function updatePassword(Users $user, string $password) : bool
    {
        $app = Di::getDefault()->getApp();

        $userApps = $user->getApps([
            'conditions' => 'apps_id = ?0',
            'bind' => [$app->getId()]
        ]);

        if (is_object($userApps)) {
            $userApps->update(['password' => $password]);
        }

        return true;
    }
}
