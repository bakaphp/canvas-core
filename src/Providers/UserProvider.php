<?php

namespace Canvas\Providers;

use Canvas\Models\Users;
use Phalcon\Di\DiInterface;
use Phalcon\Di\ServiceProviderInterface;

class UserProvider implements ServiceProviderInterface
{
    /**
     * @param DiInterface $container
     */
    public function register(DiInterface $container) : void
    {
        $container->setShared(
            'userProvider',
            function () {
                return new Users();
            }
        );
    }
}
