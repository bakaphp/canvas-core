<?php

namespace Canvas\Providers;

use Phalcon\Di\ServiceProviderInterface;
use Phalcon\Di\DiInterface;
use Canvas\Acl\Manager as AclManager;
use Phalcon\Acl;

class AclProvider implements ServiceProviderInterface
{
    /**
     * @param DiInterface $container
     */
    public function register(DiInterface $container) : void
    {
        //$config = $container->getShared('config');
        $db = $container->getShared('db');

        $container->setShared(
            'acl',
            function () use ($db) {
                $acl = new AclManager(
                    [
                        'db' => $db,
                        'roles' => 'roles',
                        'rolesInherits' => 'roles_inherits',
                        'resources' => 'resources',
                        'resourcesAccesses' => 'resources_accesses',
                        'accessList' => 'access_list'
                    ]
                );

                //default behavior
                $acl->setDefaultAction(Acl::ALLOW);

                return $acl;
            }
        );
    }
}
