<?php

namespace Gewaer\Tests\integration\library\Jobs;

use Canvas\Auth\App;
use Canvas\Models\Users;
use Exception;
use IntegrationTester;

class AppCest
{
    /**
     * the default Kanvas user can login via ecosystem login
     * not specific login.
     *
     * @param IntegrationTester $I
     * @return void
     */
    public function cantLoginToSpecificApp(IntegrationTester $I)
    {
        try {
            $user = App::login(
                'nobody@baka.io',
                'bakatest123567',
                true,
                true,
                '127.0.0.1'
            );

            $I->assertTrue(false);
        } catch (Exception $e) {
            $I->assertTrue(true);
        }
    }

    public function updateUserPassword(IntegrationTester $I)
    {
        $previousPassword = 'bakatest123567';
        $newPassword = 'bakatest123568';

        $user = Users::findFirstOrFail();
        $userPreviousPassword = $user->passsword;

        $I->assertTrue(App::updatePassword($user, $newPassword));

        //we need to compare previous password with new update passwor for the entity
    }
}
