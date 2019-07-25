<?php

declare(strict_types=1);

namespace Canvas\Auth;

use Canvas\Models\Users;
use Exception;
use stdClass;

abstract class Auth
{
    /**
     * Check the user login attems to the app.
     *
     * @param Users $user
     * @throws Exception
     * @return void
     */
    protected static function loginAttempsValidation(Users $user): bool
    {
        //load config
        $config = new stdClass();
        $config->login_reset_time = getenv('AUTH_MAX_AUTOLOGIN_TIME');
        $config->max_login_attempts = getenv('AUTH_MAX_AUTOLOGIN_ATTEMPS');

        // If the last login is more than x minutes ago, then reset the login tries/time
        if ($user->user_last_login_try && $config->login_reset_time && $user->user_last_login_try < (time() - ($config->login_reset_time * 60))) {
            $user->user_login_tries = 0; //turn back to 0 attems, succes
            $user->user_last_login_try = 0;
            $user->updateOrFail();
        }

        // Check to see if user is allowed to login again... if his tries are exceeded
        if ($user->user_last_login_try
            && $config->login_reset_time
            && $config->max_login_attempts
            && $user->user_last_login_try >= (time() - ($config->login_reset_time * 60))
            && $user->user_login_tries >= $config->max_login_attempts) {
            throw new Exception(sprintf(_('You have exhausted all login attempts.'), $config->max_login_attempts));
        }

        return true;
    }

    /**
     * Reset login tries.
     *
     * @param Users $user
     * @return boolean
     */
    protected static function resetLoginTries(Users $user): bool
    {
        $user->lastvisit = date('Y-m-d H:i:s');
        $user->user_login_tries = 0;
        $user->user_last_login_try = 0;
        return $user->updateOrFail();
    }

    /**
     * Update login tries for the given user.
     *
     * @return bool
     */
    protected static function updateLoginTries(Users $user): bool
    {
        if ($user->getId() != Users::ANONYMOUS) {
            $user->user_login_tries += 1;
            $user->user_last_login_try = time();
            return $user->updateOrFail();
        }

        return false;
    }

    /**
     * Check if the user password needs to ve rehash
     * why? php shit with the new API http://www.php.net/manual/en/function.password-needs-rehash.php.
     *
     * @param string $password
     * @return boolean
     */
    protected static function passwordNeedRehash(string $password, object $entity) : bool
    {
        $options = [
            //'salt' => mcrypt_create_iv(22, MCRYPT_DEV_URANDOM), // Never use a static salt or one that is not randomly generated.
            'cost' => 12, // the default cost is 10
        ];

        if (password_needs_rehash($entity->password, PASSWORD_DEFAULT, $options)) {
            $entity->password = self::passwordHash($password);
            $entity->updateOrFail();

            return true;
        }

        return false;
    }
}
