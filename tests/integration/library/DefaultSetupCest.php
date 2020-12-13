<?php

namespace Canvas\Tests\integration\library\Models;

use Canvas\Auth\Auth;
use Canvas\Models\Apps;
use Canvas\Models\AppsPlans;
use Canvas\Models\AppsSettings;
use Canvas\Models\Companies;
use Canvas\Models\Roles;
use Canvas\Models\Subscription;
use Canvas\Models\Users;
use Canvas\Models\UsersInvite;
use Faker\Factory;
use IntegrationTester;
use Phalcon\Security\Random;
use Baka\Auth\UserProvider;

class DefaultSetupCest
{
    private $random;

    private $email;

    private $password;

    private $app;

    private $usersInviteEmail;

    public function onContruct()
    {
        $faker = Factory::create();
        $this->random = new Random();
        $this->email = strtolower($faker->firstname . '@example.com');
        $this->password = $faker->password;
        $this->app = Apps::findFirst();
    }

    /**
     * Confirm the default apps exist.
     *
     * @param IntegrationTester $I
     *
     * @return void
     */
    public function getDefaultApp(IntegrationTester $I)
    {
        $I->assertTrue($this->app->name == Apps::CANVAS_DEFAULT_APP_NAME);
    }

    /**
     * Validate is an app has an active status or not.
     *
     * @param UnitTester $I
     *
     * @return void
     */
    public function isActive(IntegrationTester $I)
    {
        $I->assertTrue($this->app->isActive());
    }

    /**
     * Confirm the default apps exist.
     *
     * @param IntegrationTester $I
     *
     * @return void
     */
    public function getDefaultPlan(IntegrationTester $I)
    {
        $I->assertTrue(AppsPlans::getDefaultPlan() instanceof AppsPlans);
    }

    /**
     * Confirm all the apps settings.
     *
     * @param IntegrationTester $I
     *
     * @return void
     */
    public function getAllAppSettings(IntegrationTester $I)
    {
        $appSettings = AppsSettings::find([
            'conditions' => 'apps_id = ?0 and is_deleted = 0',
            'bind' => [$this->app->id]
        ]);

        //Assert true if we got the exact number of settings for the app. These are the most basic settings used by an app.
        $I->assertTrue($appSettings->count() == AppsSettings::APP_DEFAULT_SETTINGS_NUMBER);
    }

    /**
     * Get the active subscription for this company app.
     *
     * @param IntegrationTester $I
     *
     * @return void
     */
    public function getActiveSubscriptionForThisApp(IntegrationTester $I)
    {
        $I->assertTrue(Subscription::getActiveForThisApp() instanceof Subscription);
    }

    /**
     * Get all Roles of the app.
     *
     * @param IntegrationTester $I
     *
     * @return void
     */
    public function getAllRoles(IntegrationTester $I)
    {
        $role = Roles::find([
            'conditions' => 'apps_id = ?0 and is_deleted = 0',
            'bind' => [$this->app->id]
        ]);
        $I->assertTrue(is_object($role));
    }

    /**
     * Register a new Company.
     *
     * @param IntegrationTester $I
     *
     * @return void
     */
    public function registerCompanyTest(IntegrationTester $I)
    {
        $random = new Random();
        $newCompany = Companies::register($I->grabFromDi('userData'), 'TestCompany-' . $random->base58());
        $I->assertTrue($newCompany instanceof Companies);
    }

    /**
     * Signup a new user.
     *
     * @param IntegrationTester $I
     *
     * @return void
     */
    public function signupTest(IntegrationTester $I)
    {
        UserProvider::set(new Users());

        $user =  UserProvider::get();
        $user->firstname = $I->faker->firstname;
        $user->lastname = $I->faker->lastname;
        $user->email = $this->email;
        $user->password = $this->password;
        $user->defaultCompanyName = $I->faker->company;
        $user->displayname = $I->faker->firstname;

        $I->assertTrue(Auth::signUp($user) instanceof Users);
    }

    /**
     * Login the new user.
     *
     * @param IntegrationTester $I
     *
     * @return void
     */
    public function loginTest(IntegrationTester $I)
    {
        $I->assertTrue(Auth::login($this->email, $this->password, 1, 0, $I->faker()->ipv4) instanceof Users);
    }

    /**
     * Check if email does not exist on system for users invite.
     *
     * @param IntegrationTester $I
     *
     * @return void
     */
    public function emailIsValidTest(IntegrationTester $I)
    {
        $this->usersInviteEmail = $I->faker()->email;
        $I->assertTrue(UsersInvite::isValid($this->usersInviteEmail));
    }

    /**
     * Verify if users invite exists by hash.
     *
     * @param IntegrationTester $I
     *
     * @return void
     */
    public function getUsersInviteByHashTest(IntegrationTester $I)
    {
        $userInvite = new UsersInvite();
        $userInvite->companies_id = $I->grabFromDi('userData')->getDefaultCompany()->getId();
        $userInvite->users_id = $I->grabFromDi('userData')->getId();
        $userInvite->apps_id = $this->app->getId();
        $userInvite->role_id = Roles::existsById(1)->id;
        $userInvite->email = $this->usersInviteEmail;
        $userInvite->invite_hash = $this->random->base58();
        $userInvite->created_at = date('Y-m-d H:m:s');
        $I->assertTrue($userInvite->save());

        //Lets verify that it exists by looking for it by hash
        $I->assertTrue(UsersInvite::getByHash($userInvite->invite_hash) instanceof UsersInvite);
    }
}
