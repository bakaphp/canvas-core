<?php

namespace Gewaer\Tests\integration\library\Jobs;

use Canvas\Auth\App;
use Canvas\Auth\Factory;
use Canvas\Models\Users;
use Exception;
use IntegrationTester;

class AuthCest
{
    public function verifyLoginAttempts(IntegrationTester $I)
    {
        //we want to fail once
        try {
            $user = App::login(
                'nobody@baka.io',
                'bakatest1235s67',
                true,
                true,
                '127.0.0.1'
            );
        } catch (Exception $e) {
        }

        try {
            $oldUser = Users::findFirstByEmail('nobody@baka.io');
            $I->assertTrue($oldUser->user_login_tries > 0);
        } catch (Exception $e) {
            $I->assertTrue(false);
        }
    }

    public function verifyRestAttemptsCount(IntegrationTester $I)
    {
        $user = App::login(
            'nobody@baka.io',
            'bakatest123567',
            true,
            true,
            '127.0.0.1'
        );

        $I->assertTrue($user->user_login_tries == 0);
    }

    /**
     * By giving it true we are telling the factory to get use a 
     * User object since the password is the same for all apps
     *
     * @param IntegrationTester $I
     * @return void
     */
    public function getEcosystemLogin(IntegrationTester $I)
    {
        $appAuthConfig = Factory::create(true);

        $I->assertTrue($appAuthConfig instanceof Users);
    }

    /**
     * Giving it false we are telling it to give use a app
     * object where the password is uniq for that app
     *
     * @param IntegrationTester $I
     * @return void
     */
    public function getAppLogin(IntegrationTester $I)
    {
        $appAuthConfig = Factory::create(false);

        $I->assertTrue($appAuthConfig instanceof App);
    }
}
