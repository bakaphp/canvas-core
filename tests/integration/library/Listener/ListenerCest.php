<?php

namespace Canvas\Tests\integration\library\Listener;

use Canvas\Listener\Company;
use Canvas\Listener\Notification;
use Canvas\Listener\User;
use IntegrationTester;

class ListenerCest
{
    public function kanvasCoreDefaultListener(IntegrationTester $I)
    {
        $I->assertTrue(is_object(new Company()));
        $I->assertTrue(is_object(new Notification()));
        $I->assertTrue(is_object(new User()));
    }
}
