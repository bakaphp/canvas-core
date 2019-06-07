<?php

namespace Gewaer\Tests\integration\library\Models;

use Canvas\Models\Apps;
use IntegrationTester;
use Canvas\Providers\ConfigProvider;
use Phalcon\Di\FactoryDefault;
use Canvas\Models\Roles;
use Canvas\Models\Companies;
use Gewaer\Models\Users;
use Page\Data;

class RolesCest
{
    /**
     * Confirm the default apps exist
     *
     * @param IntegrationTester $I
     * @return void
     */
    public function getByAppName(IntegrationTester $I)
    {
        $diContainer = new FactoryDefault();

        $provider = new ConfigProvider();
        $provider->register($diContainer);

        $company = Companies::findFirst(Users::findFirstByEmail(Data::loginJson()['email'])->default_company);
        $role = Roles::getByAppName('Default.Admins', $company);

        $I->assertTrue($role->name == 'Admins');
    }
}
