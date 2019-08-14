<?php

namespace Gewaer\Tests\integration\library\Models;

use IntegrationTester;
use Canvas\Providers\ConfigProvider;
use Phalcon\Di\FactoryDefault;
use Canvas\Models\Roles;
use Canvas\Models\Companies;
use Phalcon\Acl\Role as AclRole;

class RolesCest
{
    /**
     * Check if the role existe in the db
     *
     * @param IntegrationTester $I
     * @return void
     */
    public function exist(IntegrationTester $I)
    {
        $roleExists = Roles::exist(new AclRole('Admins-Example'));
        $I->assertTrue(gettype($roleExists) == 'integer');
    }

    /**
     * Get the entity by its name
     *
     * @param IntegrationTester $I
     * @return void
     */
    public function getByName(IntegrationTester $I)
    {
        $role = Roles::getByName('Users');
        $I->assertTrue($role instanceof Roles);
    }

    /**
     * Get the entity by its id
     *
     * @param IntegrationTester $I
     * @return void
     */
    public function getById(IntegrationTester $I)
    {
        $role = Roles::getById(1);
        $I->assertTrue($role instanceof Roles);
    }

    /**
     * Get the Role by it app name
     *
     * @param IntegrationTester $I
     * @return void
     */
    public function getByAppName(IntegrationTester $I)
    {
        $company = Companies::getDefaultByUser($I->grabFromDi('userData'));

        $role = Roles::getByAppName('Default.Users', $company);
        $I->assertTrue($role instanceof Roles);
    }

    /**
     * Duplicate a role with it access list
     *
     * @param IntegrationTester $I
     * @return void
     */
    public function copy(IntegrationTester $I)
    {
        $role = Roles::findFirst(1);
        $I->assertTrue($role->copy() instanceof Roles);
    }

    /**
     * Add inherit to a given role
     *
     * @param IntegrationTester $I
     * @return void
     */
    public function addInherit(IntegrationTester $I)
    {
        $I->assertTrue(gettype(Roles::addInherit('Users', 'Default.Users')) == 'boolean');
    }

    /**
     * Check if role exists by its id
     *
     * @param IntegrationTester $I
     * @return void
     */
    public function existsById(IntegrationTester $I)
    {
        $I->assertTrue(Roles::existsById(1) instanceof Roles);
    }
}
