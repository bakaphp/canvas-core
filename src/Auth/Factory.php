<?php

declare(strict_types=1);

namespace Canvas\Auth;

class Factory
{
    /**
     * Create the Auth factory.
     *
     * @param bool $ecosystemAuth
     *
     * @return Auth|App
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
