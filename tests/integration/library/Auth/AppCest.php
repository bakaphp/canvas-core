<?php

namespace Gewaer\Tests\integration\library\Jobs;

use Canvas\Auth\App;
use Canvas\Hashing\Password;
use Canvas\Models\Apps;
use Canvas\Models\Companies;
use Canvas\Models\Users;
use Exception;
use IntegrationTester;
use Page\Data;
use Phalcon\Security\Random;

class AppCest
{
    private $app;

    /**
     * Constructor.
     *
     * @return void
     */
    public function onContruct()
    {
        $this->app = Apps::findFirst();
    }

    /**
     * Register a new Company.
     *
     * @param IntegrationTester $I
     *
     * @return void
     */
    public function setCompanyTest(IntegrationTester $I)
    {
        $random = new Random();
        $newCompany = Companies::register($I->grabFromDi('userData'), 'TestCompany-' . $random->base58());
        $I->assertTrue($newCompany instanceof Companies);
    }

    public function canSetAppPassword(IntegrationTester $I)
    {
        $oldUser = Users::findFirstByEmail(Data::loginJsonDefaultUser()['email']);
        $I->assertTrue(
            App::updatePassword($oldUser, Password::make(Data::loginJsonDefaultUser()['password']))
        );
    }

    /**
     * the default Kanvas user can login via ecosystem login
     * not specific login.
     *
     * @param IntegrationTester $I
     *
     * @return void
     */
    public function cantLoginToSpecificApp(IntegrationTester $I)
    {
        try {
            $user = App::login(
                Data::loginJsonDefaultUser()['email'],
                'bakatest123567s',
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
        //if you are using a ecosystem aut we cant run this test
        if ($this->app->ecosystem_auth) {
            $I->assertTrue(true);
            return;
        }

        $previousPassword = Data::loginJsonDefaultUser()['password'];
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
     * we run it after updateuserpass to make sure we have the correct pass for this speciifc app.
     *
     * @param IntegrationTester $I
     *
     * @return boolean
     */
    public function canLoginToSpecificApp(IntegrationTester $I)
    {
        try {
            $user = App::login(
                Data::loginJsonDefaultUser()['email'],
                Data::loginJsonDefaultUser()['password'],
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
