<?php

namespace Gewaer\Tests\integration\library\Models;

use Canvas\Models\UsersInvite;
use Canvas\Models\Roles;
use Canvas\Models\Apps;
use IntegrationTester;
use Phalcon\Security\Random;

class UsersInviteCest
{
    /**
     * App.
     *
     * @var object
     */
    private $app;
    /**
     * random value.
     *
     * @var string
     */
    private $random;

    /**
     * Users Invite Email.
     *
     * @var string
     */
    private $usersInviteEmail;

    /**
     * Constructor.
     *
     * @return void
     */
    public function onContruct()
    {
        $this->random = new Random();
        $this->app = Apps::getACLApp(Apps::CANVAS_DEFAULT_APP_NAME);
    }

    /**
     * Check if email does not exist on system for users invite.
     *
     * @param IntegrationTester $I
     * @return void
     */
    public function emailIsValidTest(IntegrationTester $I): void
    {
        $this->usersInviteEmail = $this->random->base58() . '@example.com';
        $I->assertTrue(UsersInvite::isValid($this->usersInviteEmail));
    }

    /**
     * Verify if users invite exists by hash.
     *
     * @param IntegrationTester $I
     * @return void
     */
    public function getUsersInviteByHashTest(IntegrationTester $I): void
    {
        $userInvite = new UsersInvite();
        $userInvite->companies_id = $I->grabFromDi('userData')->currentCompanyId();
        $userInvite->users_id = $I->grabFromDi('userData')->getId();
        $userInvite->app_id = $this->app->id;
        $userInvite->role_id = Roles::existsById(1)->id;
        $userInvite->email = $this->usersInviteEmail;
        $userInvite->invite_hash = $this->random->base58();
        $userInvite->created_at = date('Y-m-d H:m:s');
        $I->assertTrue($userInvite->save());

        //Lets verify that it exists by looking for it by hash
        $I->assertTrue(UsersInvite::getByHash($userInvite->invite_hash) instanceof UsersInvite);
    }
}
