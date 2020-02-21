<?php

namespace Gewaer\Tests\integration\library\Jobs;

use Canvas\Auth\App;
use Canvas\Hashing\Password;
use Canvas\Models\Users;
use Exception;
use IntegrationTester;
use PhpParser\Node\Expr\Instanceof_;

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

    public function updateUserAppPassword(IntegrationTester $I)
    {
        $previousPassword = 'bakatest123567';
        $newPassword = 'bakatest123568';

        $user = Users::findFirstOrFail();
        $userAppData = $user->getApp([
            'conditions' => 'companies_id = :id:',
            'bind' => [
                'id' => $user->currentCompanyId()
            ]
        ]);

        $userPreviousPassword = $userAppData->password;
        $I->assertTrue(App::updatePassword($user, Password::make($newPassword)));

        $userAppDataAgain = $user->getApp([
            'conditions' => 'companies_id = :id:',
            'bind' => [
                'id' => $user->currentCompanyId()
            ]
        ]);

        $I->assertNotEquals($userPreviousPassword, $userAppDataAgain->password);

        //return pass to previous state
        App::updatePassword($user, Password::make($previousPassword));

        $I->assertEquals($userPreviousPassword, $userAppData->password);
    }

    /**
     * Can login to especific app
     * we run it after updateuserpass to make sure we have the correct pass for this speciifc app
     *
     * @param IntegrationTester $I
     * @return boolean
     */
    public function canLoginToSpecificApp(IntegrationTester $I)
    {
        try {
            $user = App::login(
                'nobody@baka.io',
                'bakatest123567',
                true,
                true,
                '127.0.0.1'
            );

            $I->assertTrue($user instanceof Users);
        } catch (Exception $e) {
            $I->assertTrue(false);
        }
    }
}
