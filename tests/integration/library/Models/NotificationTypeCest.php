<?php

namespace Gewaer\Tests\integration\library\Models;

use Canvas\Models\NotificationType;
use Canvas\Models\Users;
use IntegrationTester;

class NotificationTypeCest
{
    /**
     * Get the notification by its key
     * by defautl in any kanvas app the key will be its classname.
     *
     * @param IntegrationTester $I
     * @return void
     */
    public function getByKey(IntegrationTester $I)
    {
        $notificationType = NotificationType::getByKey('Canvas\Notifications\Users');
        $I->assertTrue($notificationType instanceof NotificationType);
    }

    /**
     * Get the notification by its key or created it
     *
     * @param IntegrationTester $I
     * @return void
     */
    public function getByKeyOrCreate(IntegrationTester $I)
    {
        $model = new Users();

        $notificationType = NotificationType::getByKeyOrCreate('User-Key', $model);
        $I->assertTrue($notificationType instanceof NotificationType);
    }
}
