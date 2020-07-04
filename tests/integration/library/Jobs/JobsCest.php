<?php

namespace Gewaer\Tests\integration\library\Jobs;

use Canvas\Cli\Jobs\PushNotifications;
use Baka\Jobs\PendingDispatch;
use Canvas\Models\Users;
use Canvas\Notifications\PushNotification;
use IntegrationTester;

class JobsCest
{
    public function jobToQueue(IntegrationTester $I)
    {
        $user = Users::findFirst();
        $pushNotification = new PushNotification($user, 'Test Jobs', 'Test Canvas Jobs');

        $I->assertTrue(PushNotifications::dispatch($pushNotification) instanceof PendingDispatch);
    }
}
