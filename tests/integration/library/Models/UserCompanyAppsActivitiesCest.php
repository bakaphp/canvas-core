<?php

namespace Canvas\Tests\integration\library\Models;

use Canvas\Models\UserCompanyAppsActivities;
use IntegrationTester;

class UserCompanyAppsActivitiesCest
{
    /**
     * Set a setting for the given app.
     *
     * @param IntegrationTester $I
     *
     * @return void
     */
    public function set(IntegrationTester $I)
    {
        $userCompanyAppsActivities = UserCompanyAppsActivities::set('mQsVRvorhqBJOijxkC4MB4hHFVcVTJeef', 'example');
        $I->assertTrue(gettype($userCompanyAppsActivities) == 'boolean');
    }

    /**
     * Get the value of the settins by it key.
     *
     * @param IntegrationTester $I
     *
     * @return void
     */
    public function get(IntegrationTester $I)
    {
        $userCompanyAppsActivities = UserCompanyAppsActivities::get('mQsVRvorhqBJOijxkC4MB4hHFVcVTJeef');
        $I->assertTrue(gettype($userCompanyAppsActivities) == 'string');
    }
}
