<?php

namespace Gewaer\Tests\integration\library\Models;

use Canvas\Models\UserCompanyApps;
use IntegrationTester;

class UserCompanyAppsCest
{
    /**
     * Get the current company app
     *
     * @param IntegrationTester $I
     * @return void
     */
    public function getCurrentApp(IntegrationTester $I)
    {
        $userCompanyApps = UserCompanyApps::getCurrentApp();
        $I->assertTrue($userCompanyApps instanceof UserCompanyApps);
    }
}
