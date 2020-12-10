<?php

declare(strict_types=1);

namespace Canvas\Auth;

use Baka\Auth\Auth;

class Factory
{
    /**
     * Create the Auth factory.
     *
     * @param int $ecosystem_auth
     *
     * @return Auth
     */
    public static function create(bool $ecosystemAuth) : Auth
    {
        $user = null;
        switch ($ecosystemAuth) {
            case false:
                $user = new App();
                break;

            default:
                $user = new Auth();
                break;
        }

        return $user;
    }
}
