<?php

namespace Gewaer\Tests\integration\library\Models;

use Canvas\Models\RegisterRoles;
use IntegrationTester;
use Canvas\Providers\ConfigProvider;
use Phalcon\Di\FactoryDefault;
use Phalcon\Security\Random;

class RegisterRolesCest
{
    /**
     * get register roles by uuid
     *
     * @param IntegrationTester $I
     * @return void
     */
    public function getByUuid(IntegrationTester $I)
    {
        $newRegisterRole = new RegisterRoles();
        $newRegisterRole->roles_id = 2;
        $newRegisterRole->saveOrFail();
        $I->assertTrue($newRegisterRole instanceof RegisterRoles);

        $registerRole = RegisterRoles::getByUuid($newRegisterRole->uuid);
        $I->assertTrue($registerRole instanceof RegisterRoles);
    }
}
