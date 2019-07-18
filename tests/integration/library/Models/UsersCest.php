<?php

namespace Gewaer\Tests\integration\library\Models;

use Canvas\Models\Apps;
use Canvas\Models\Users;
use Canvas\Models\Subscription;
use IntegrationTester;
use Canvas\Providers\ConfigProvider;
use Phalcon\Di\FactoryDefault;

class UsersCest
{
    /**
     * Confirm the default apps exist.
     *
     * @param IntegrationTester $I
     * @return void
     */
    public function subscription(IntegrationTester $I)
    {
        $app = Apps::getACLApp(Apps::CANVAS_DEFAULT_APP_NAME);
        $I->assertTrue($app->name == Apps::CANVAS_DEFAULT_APP_NAME);
    }

    /**
     * Get Current Company Id
     *
     * @param IntegrationTester $I
     * @return void
     */
    public function currentCompanyId(IntegrationTester $I)
    {
        $I->assertTrue(gettype($I->grabFromDi('userData')->currentCompanyId()) == 'integer');
    }

    /**
     * Get Current Company Branch Id
     *
     * @param IntegrationTester $I
     * @return void
     */
    public function currentCompanyBranchId(IntegrationTester $I)
    {
        $I->assertTrue(gettype($I->grabFromDi('userData')->currentCompanyBranchId()) == 'integer');
    }

    /**
     * Get Associated Apps
     *
     * @param IntegrationTester $I
     * @return void
     */
    public function getAssociatedApps(IntegrationTester $I)
    {
        $I->assertTrue(gettype($I->grabFromDi('userData')->getAssociatedApps()) == 'array');
    }

    /**
     * Get Associated Companies
     *
     * @param IntegrationTester $I
     * @return void
     */
    public function getAssociatedCompanies(IntegrationTester $I)
    {
        $I->assertTrue(gettype($I->grabFromDi('userData')->getAssociatedApps()) == 'array');
    }

    /**
     * Get By User Activation Email
     *
     * @param IntegrationTester $I
     * @return void
     */
    public function getByUserActivationEmail(IntegrationTester $I)
    {
        $I->assertTrue($I->grabFromDi('userData')->getByUserActivationEmail($I->grabFromDi('userData')->user_activation_email) instanceof Users);
    }

    /**
     * Start Free Trial
     *
     * @param IntegrationTester $I
     * @return void
     */
    public function startFreeTrial(IntegrationTester $I)
    {
        $I->assertTrue($I->grabFromDi('userData')->startFreeTrial() instanceof Subscription);
    }
}
