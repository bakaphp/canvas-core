<?php

namespace Canvas\Tests\integration\library\Models;

use Canvas\Models\Notifications;
use IntegrationTester;

class NotificationsCest
{
    public function markAsRead(IntegrationTester $I)
    {
        $markAsRead = Notifications::markAsRead($I->grabFromDi('userData'));

        $I->assertTrue($markAsRead);
    }

    public function totalUnRead(IntegrationTester $I)
    {
        $total = Notifications::totalUnRead($I->grabFromDi('userData'));

        $I->assertTrue(is_int($total));
    }

}
