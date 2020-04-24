<?php

declare(strict_types=1);

namespace Canvas\Auth;

use Canvas\Models\Users;

class Factory
{
    /**
     * Create the Auth factory
     *
     * @param integer $ecosystem_auth
     * @return Auth
     */
    public static function create(bool $ecosystemAuth)
    {
        $user = null;
        switch ($ecosystemAuth) {
            case false:
                $user = new App();
                break;
            
            default:
                $user = new Users();
                break;
        }

        return $user;
    }
    
}