<?php

namespace Gewaer\Tests\integration\library\Models;

use Canvas\Models\Companies;
use Canvas\Models\UserCompanyAppsActivities;
use IntegrationTester;
use Phalcon\Security\Random;

class UserCompanyAppsActivitiesCest
{
    /**
     * Set a setting for the given app
     *
     * @param IntegrationTester $I
     * @return void
     */
    public function set(IntegrationTester $I)
    {
        $random = new Random();
        $userCompanyAppsActivities = UserCompanyAppsActivities::set($random->base58(), 'example');
        $I->assertTrue(gettype($userCompanyAppsActivities) == 'boolean');
    }

    /**
     * Get the value of the settins by it key
     *
     * @param IntegrationTester $I
     * @return void
     */
    public function get(IntegrationTester $I)
    {
        $userCompanyAppsActivities = UserCompanyAppsActivities::get('mQsVRvorhqBJOijxkC4MB4hHFVcVTJia');
        $I->assertTrue(gettype($userCompanyAppsActivities) == 'string');
    }
}
