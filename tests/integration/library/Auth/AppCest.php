<?php

namespace Gewaer\Tests\integration\library\Jobs;

use Canvas\Auth\App;
use Canvas\Cli\Jobs\PushNotifications;
use Canvas\Jobs\PendingDispatch;
use Canvas\Models\Users;
use Canvas\Notifications\PushNotification;
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
}
