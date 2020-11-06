<?php

namespace Gewaer\Tests\integration\library\Acl;

use Canvas\Acl\Manager as AclManager;
use Canvas\Models\Users;
use Canvas\Models\Roles;
use Canvas\Providers\AclProvider;
use Canvas\Providers\ConfigProvider;
use Canvas\Providers\DatabaseProvider;
use IntegrationTester;
use Page\Data;
use Phalcon\Di\FactoryDefault;

class AclCest
{
    /**
     * Initiliaze ACL.
     *
     * @return void
     */
    protected function aclService(IntegrationTester $I) : AclManager
    {
        // $diContainer = new FactoryDefault();
        $provider = new ConfigProvider();
        $provider->register($I->grabDi());
        $provider = new DatabaseProvider();
        $provider->register($I->grabDi());
        $provider = new AclProvider();
        $provider->register($I->grabDi());

        return $I->grabFromDi('acl');
    }

    public function validateAclService(IntegrationTester $I)
    {
        $acl = $this->aclService($I);
        $I->assertTrue($acl instanceof AclManager);
    }

    public function checkCreateRole(IntegrationTester $I)
    {
        $acl = $this->aclService($I);

        $I->assertTrue($acl->addRole(new \Phalcon\Acl\Role('Admins')));
    }

    public function checkAddResource(IntegrationTester $I)
    {
        $acl = $this->aclService($I);

        $I->assertTrue($acl->addComponent('Default.Users', ['read', 'list', 'create', 'update', 'delete']));
    }

    public function checkAllowPermission(IntegrationTester $I)
    {
        $acl = $this->aclService($I);

        $I->assertTrue(is_null($acl->allow('Admins', 'Default.Users', ['read', 'list', 'create'])));
    }

    public function checkDenyPermission(IntegrationTester $I)
    {
        $acl = $this->aclService($I);

        $I->assertTrue(is_null($acl->deny('Admins', 'Default.Users', ['update', 'delete'])));
    }

    public function checkIsAllowPermission(IntegrationTester $I)
    {
        $acl = $this->aclService($I);

        $I->assertTrue($acl->isAllowed('Admins', 'Default.Users', 'read'));
    }

    public function checkIsDeniedPermission(IntegrationTester $I)
    {
        $acl = $this->aclService($I);

        $I->assertFalse($acl->isAllowed('Admins', 'Default.Users', 'update'));
    }

    public function checkSetAppByRole(IntegrationTester $I)
    {
        $acl = $this->aclService($I);

        $I->assertTrue($acl->addRole('Default.Admins'));
    }

    public function checkUsersAssignRole(IntegrationTester $I)
    {
        $acl = $this->aclService($I);
        $userData = Users::findFirstByEmail(Data::loginJsonDefaultUser()['email']);

        $I->assertTrue($userData->assignRole('Default.Admins'));
    }

    public function checkUsersAssignRoleById(IntegrationTester $I)
    {
        $acl = $this->aclService($I);
        $userData = Users::findFirstByEmail(Data::loginJsonDefaultUser()['email']);
        $adminRole = Roles::findFirst(1);
        $userRole = Roles::findFirst(2);

        $I->assertTrue($userData->assignRoleById($userRole->getId()));
        $I->assertTrue($userData->assignRoleById($adminRole->getId()));
    }

    public function checkUsersHasPermission(IntegrationTester $I)
    {
        $acl = $this->aclService($I);
        $userData = Users::findFirstByEmail(Data::loginJsonDefaultUser()['email']);

        $I->assertTrue($userData->can('Users.create'));
    }

    public function checkUsersDoesntHavePermission(IntegrationTester $I)
    {
        $acl = $this->aclService($I);
        $userData = Users::findFirstByEmail(Data::loginJsonDefaultUser()['email']);

        $I->assertFalse($userData->can('Users.delete'));
    }

    public function checkUsersRemoveRole(IntegrationTester $I)
    {
        $acl = $this->aclService($I);
        $userData = Users::findFirstByEmail(Data::loginJsonDefaultUser()['email']);

        $I->assertTrue($userData->assignRole('Default.Admins'));

        $I->assertTrue($userData->removeRole('Default.Admins'));

        $I->assertTrue($userData->assignRole('Default.Admins'));
    }
}
